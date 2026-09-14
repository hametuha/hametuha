---
name: prod-sync
description: "ローカル環境のプラグイン・WordPressコアのバージョンを本番（hametuha.com）と対話形式で揃える。本番の実態を読み取り、composer.lock と突き合わせ、差分を1本ずつ判断して composer.json に反映する。バージョンを上げる/上げないの判断材料を集めるところまでを含む。"
compatibility: "hametuha リポジトリ専用。wp-cli の @production エイリアスと Docker 環境が前提。"
allowed-tools: Read, Write, Edit, Bash, Glob, Grep, AskUserQuestion
---

# 本番とバージョンを揃える

ローカルを本番の再現に近づけるための対話型スキル。**差分を機械的に潰すのではなく、1本ずつ「上げるべきか」を判断する**のが目的。

## 大前提：本番が古いことには理由があることがある

**このスキルで最も重要な原則。** 本番が古いバージョンで止まっているとき、「遅れている」と決めつけて上げてはいけない。**意図的に止めている**可能性を必ず疑うこと。

実例（2026-09）: 本番の `hamelp` が 2.1.0 で update available 2.6.0 だったため「遅れているだけ」と判断しかけた。実際は **hamelp 2.4.0 以降の AI Overview が WordPress 7.0 同梱の `wp_ai_client_prompt()` に依存**しており、6.8 の本番で先にプラグインだけ上げると、**fatal も警告も出さずに AI 機能だけが消える**ところだった。

```php
// wp-ai-client is bundled in WordPress 7.0+. If it's not available
// (older WP), this feature is disabled silently.
if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
	return;
}
```

**判定方法**: 対象プラグインの中を `function_exists` / `class_exists` のガードで grep する。「どのコア機能・どのプラグインに依存しているか」がコメント付きで書かれていることが多い。

```bash
rg -n "function_exists\(|class_exists\(" plugins/<slug>/ | head -20
```

ガードに引っかかる依存があれば、**順序はコア → プラグイン**。逆にすると機能が静かに消える。

## 前提条件

- `wp-cli.local.yml` に `@production` エイリアスがあること（無ければ CLAUDE.md の手順で作る）。認証情報は書かず `~/.ssh/config` の Host エイリアス名だけを書く
- Docker が起動していること（`composer start`）
- 作業ブランチにいること。`git branch --show-current` で master でないことを必ず確認

## 手順

### 0. 宣言と実体が一致しているか確認する（最初にやる）

**`wp/` と `plugins/` は .gitignore 済みのビルド生成物で、ブランチを切り替えても追随しない。** 別ブランチでの作業結果がディスクに残ったままになる。この状態で比較すると **composer.lock（宣言）とディスク（実体）が食い違い、比較結果そのものが信用できなくなる。**

```bash
# コア: 宣言と実体
grep -n '"johnpbloch/wordpress"' composer.json
grep -m1 'wp_version = ' wp/wp-includes/version.php

# プラグイン: 代表数本の宣言と実体
for p in contact-form-7 gianism query-monitor; do
  printf "%-18s lock:%-10s disk:%s\n" "$p" \
    "$(php -r '$l=json_decode(file_get_contents("composer.lock"),true); foreach($l["packages"] as $x){ if($x["name"]==="wpackagist-plugin/'$p'") echo $x["version"]; }')" \
    "$(grep -m1 -i '^ \* Version:' plugins/$p/*.php 2>/dev/null | head -1 | sed 's/.*[Vv]ersion: *//')"
done
```

ずれていたら `composer install` で宣言どおりに戻してから進む。**ただしこれはディスク上の環境を巻き戻す**ので、別ブランチの作業を保持したい場合はそちらへ切り替えてから実行すること。

実例（2026-09-14）: master 起点のブランチでこのスキルを実行したところ、composer.json は WP 6.8.6 なのにディスクは 7.1、`contact-form-7` は lock 5.7.7 に対し実体 6.1.7 だった（別ブランチの作業結果が残っていた）。**「未管理9本」と報告されたプラグインも実際にはディスクに存在していた。**

### 0.5. 同じ作業が既に別ブランチ/PRで進んでいないか確認する

```bash
gh pr list --state open --limit 10
```

差分がごっそり出たときは、**それを解消する PR が既に開いていないか**を疑う。あるならそのブランチに切り替えて続きをやる。master 起点で作り直すと、重複した2つ目の実装ができてコンフリクトの種になる。

### 1. 本番の実態を取得する

```bash
wp @production core version
wp @production plugin list --fields=name,status,version --format=json \
  | grep -v '^PHP Warning' > /tmp/prod.json
```

`plugin list` の **version 列も必ず目視する**。`nightly` のような値が出ていたら、そのプラグインのリリース事故（ヘッダーのバージョン置換漏れ）。実例: hameslack 2.2.0。

### 2. composer.lock と突き合わせる

```bash
php .claude/skills/prod-sync/scripts/compare-plugins.php /tmp/prod.json
```

5分類で出る。除外リストはスクリプト冒頭の `EXCLUDED` 定数にあり、**CLAUDE.md の「プラグイン管理方針」の表と対になっている**。

### 3. 差分を1本ずつ対話で判断する

ここがこのスキルの本体。**まとめて `composer update` しない。** 分類ごとに扱いが違う。

| 分類 | 既定の動き | 確認すること |
|---|---|---|
| ローカルが古い | 本番に合わせて上げる | 制約が本番のバージョンに**到達できるか**。`^3.16` のまま 4.0.7 にはならない |
| ローカルが新しい | **止める。ユーザーに理由を聞く** | 本番が意図的に止めている可能性。上記「大前提」を参照 |
| 未管理 | composer に載せるか、除外するか | CLAUDE.md の判断基準（①ローカルで動くか ②再現する価値があるか ③どちらでもなければ載せる） |
| composer にあるが本番に無い | 削除、または require-dev へ | ローカル専用ツールなら require-dev が正しい |

固定ピン（`"5.7.7"`）や無制約（`"*"`）を見つけたら、**なぜそうなっているかをユーザーに確認してから**キャレットに直す。ピンには理由があることがある。

未管理プラグインの入手経路は次の順で調べる。

```bash
# wp.org にあるか
curl -s "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=<slug>" | head -c 200
# wpackagist にあるか
composer show --all wpackagist-plugin/<slug> 2>&1 | grep '^versions'
# 自社 GitHub にあるか（リリースzipがあれば composer.json の repositories に package 型で追加できる）
gh release view -R hametuha/<slug> --json tagName,assets
```

**どこにも無い場合は、管理下に置く方法を探すのではなく本番から消せないかを検討する。** 取得経路が無い＝更新経路も無い＝脆弱性が出ても直せない。実例: `maintenance-mode` は 2017 年に wp.org で公開停止されたまま 9 年間 active だった。

### 4. 適用する

```bash
# 制約を直してから解決する
composer update "wpackagist-plugin/*" --with-all-dependencies
# コアを上げる場合（.env と composer.json の両方を更新する）
./bin/set-wordpress-version.sh 7.1.0
composer update johnpbloch/wordpress
```

コアを上げたらテストスイートも入れ直す。

```bash
rm -rf wp-tests && ./bin/install-wp-tests.sh
composer restart   # ← 必須。理由は下の「落とし穴」
```

### 5. 検証する（4点セット・省略しない）

```bash
composer test                              # PHPUnit
composer audit                             # 脆弱性
vendor/bin/phpcs --standard=phpcs.ruleset.xml themes/hametuha mu-plugins --report=summary
```

**phpcs はゼロを目指さない。** このリポジトリは master 時点で 350 errors / 413 warnings / 155ファイルあり、CI も赤が常態。**変更前後で件数が増えていないこと**を確認する（必要なら `git stash` で A/B 計測する）。

4点目が最も重要。**ブラウザでコンソールを見る。**

```
Playwright MCP で https://hametuha.info/ と https://hametuha.info/help/ を開き、
browser_console_messages でエラーを確認する。
```

アセットの URL 生成ミスやエンキュー漏れは **PHPUnit も phpcs も composer audit も検出しない**。実例: hameslack 2.2.0 の 404 は、静的解析が全部グリーンの状態でブラウザだけが見つけた。エラーが出たら、**そのプラグインを無効化して再読込し、今回の変更由来か既存かを A/B で切り分ける**。

### 6. 記録する

- **除外を増やしたら CLAUDE.md の除外表と `scripts/compare-plugins.php` の `EXCLUDED` を両方更新する。** 除外は composer.json に痕跡が残らないため、この2つが唯一の記録
- 「本番で現役だがローカルに入れていない」ものは、**単なる除外と混ぜずに「既知の穴」として書く**。3年後に理由が分からなくなるのを防ぐ
- コミットは意味の単位で分ける（プラグイン整合 / コア更新 / バグ修正）。`git cc-commit "..."` を使う

### 7. 本番への反映手順を書き出す

本番のプラグインはデプロイ対象外（デプロイはテーマのみ）なので、**本番側は手作業**になる。PR 本文に順序を明記すること。依存の向きがあるときは特に。

## 落とし穴（すべて実際に踏んだもの）

**ブランチを切り替えても `wp/` と `plugins/` は変わらない**
.gitignore 済みのため Git が管理していない。composer.lock を読む比較スクリプトは「宣言」を見るが、実際に動いているのは「実体」。手順 0 を飛ばすと、**比較結果を信じて不要な更新をかける**ことになる。

**Docker の bind mount はディレクトリを作り直すと切れる**
`rm -rf wp-tests` のようにマウント元を削除・再作成したら **必ず `composer restart`**。ホストにファイルがあるのにコンテナからは空に見える。症状は「Could not find /tmp/wordpress-tests-lib/...」のような**フォールバック先のパス**のエラーとして出るため原因が見えにくい。

**この環境の `sed` は GNU 版**
macOS 流儀の `sed -i '' -e ...` は `''` がファイル名扱いになり落ちる。`sed -i` と書く。

**`rg -rn` の `-r` は `--replace`**
ripgrep で行番号付き検索は `rg -n`。`-rn` と書くとマッチが "n" に置換されて出力が壊れる。

**`wp_supports_ai()` はプロバイダの有無を見ていない**
既定で常に true を返すだけ。AI の可用性は `AiClient::defaultRegistry()->getRegisteredProviderIds()` で判定する。

## やってはいけないこと

- **本番に対する破壊的操作**（`plugin delete` / `plugin update` / `db query` の UPDATE 等）は実行しない。手順を提示してユーザーに実行してもらう
- 許可されているのは読み取りのみ（`plugin list` / `core version` / `option get` 等）
- ローカルが本番より新しいときに、**確認なしでローカルを巻き戻さない**
- 本番の API キーや認証情報を出力しない。必要なのは「どこから読まれているか」であって値ではない

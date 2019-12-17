hametuha
========

[破滅派](https://hametuha.com)のWordPressテーマです。



## 貢献の方法

破滅派に貢献をしたいという奇特な方がいらっしゃったら、以下の方法で貢献いただけますと嬉しいです。

## イシューを立てる

イシュー（課題）を立てていただくと、「あ、そういう問題があるのだな」と気づくことができます。やり方は[Githubのマニュアル](https://help.github.com/articles/creating-an-issue/)をご覧ください。

## プルリクエストを送る

破滅派ではプルリクエストを受け付けています。やり方は[Githubのマニュアル](https://help.github.com/articles/creating-a-pull-request/)をご覧ください。

以下にソースコードを修正するための最低限の情報を記載します。

### Getting Started

hametuhaは多くがPHPコードですが、デザインだけ修正することもできます。以下、そのやり方を説明します。

まず、破滅派は[Twitter Bootstrap](http://getbootstrap.com)というCSSフレームワークを利用しています。

このカスタマイズをちょこちょこ行うだけでも、デザインの修正ができます。必要なのは[NodeJS](https://nodejs.org/en/)および[npm](https://www.npmjs.com)です。また、[Gulp](http://gulpjs.com)も使うので、インストール`npm install -g gulp`しておいてください。

```
# Gitリポジトリをクローン
git clone git@github.com:hametuha/hametuha.git
# 移動
cd hametuha
# npm をインストール
npm install
# ビルドを行うと、JSがnode_modulesからコピーされます。
npm run package
# ファイルを監視して、変更がある度にコンパイルします。
npm run watch
# ブラウザに表示します。
npm run display
```

これらのコマンドを発行すると、`hametuha/assets`フォルダにhtmlファイルがぶわーっと書きだされ、デザインの確認ができるようになります。

`hametuha/assets/jade`フォルダにあるファイルを変更すると、HTMLが変更され、`hametuha/assets/sass`フォルダにあるファイルを変更すると、CSSが書き出されます。

### Advanced

PHPを修正する場合、ダミーデータが入っていないとどうしようもないのですが、とりあえず動かすことはできます。

必要な機能は[composer](https://getcomposer.org)です。

```
# hametuhaフォルダ内で実行
composer install
```

これで必要な機能がインストールされ、利用可能になるはずです。

## ライセンス

以下の理由により、[GPL v3 or later](https://github.com/hametuha/hametuha/blob/master/LICENSE.md)でソースコードを公開しています。

- 誰かが参考にできるかもしれないので。
- もし[コミッター](https://github.com/fumikito)が死んだら、誰かが引き継いでくれるかもしれないので。

その他の理由により利用される場合も、ライセンスを順守していただければ大丈夫です。

ロゴ画像及びサイト名については著作権を放棄していませんので、無許可で使わないでください。

## コンタクト

聞きたい事がある場合は、破滅派のアカウントからコンタクトをとってください。

- [お問い合わせ](https://hametuha.com/inquiry/)
- [Twitter](https://twitter.com/hametuha/)
- [Facebook](https://www.facebook.com/hametuha.inc/)
- [Slack](https://hametuha.slack.com/)

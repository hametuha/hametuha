<hr />
<section>
    <h3>報酬の仕組み</h3>
    <ul class="collection">

        <li class="collection-item">
            <strong class="title">ロイヤリティ</strong>
            <p>
                Amazonから破滅派に振り込まれる金額です。
                このうち、<a class="alert-link" href="<?= home_url( '/contract/ebook-agency-contract/' ) ?>">規約に書いてある手数料</a>
                を引いたものがあなたの収益となります。
                源泉徴収を行いますので、差し引かれた分は確定申告で取り返してください。
            </p>
        </li>
        <li class="collection-item">
            <strong class="title">支払い時期</strong>
            <p>
                毎月15日に集計を取り、月末までに振り込みます。
            </p>
        </li>
        <li class="collection-item">
            <strong class="title">支払い下限額</strong>
            <p>
                確定した報酬の合計額が<strong>&yen; <?= number_format_i18n( hametuha_minimum_payment() ) ?>に満たない場合、入金は繰り越し</strong>となります。ご了承ください。
            </p>
        </li>
        <li class="collection-item">
            <strong class="title">アカウントの削除</strong>
            <p>
                確定報酬が残った段階でアカウントを削除してしまうと、入金されません。ご注意ください。
            </p>
        </li>
    </ul>
</section>

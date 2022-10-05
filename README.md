# 概要
WEB+DB PRESS Vol.131 掲載の、「PHPで複雑さに立ち向かう 【第9回】PHPによる非同期・並行処理の実装 ノンブロッキングI/O、イベントループ、Promise、コルーチン」のサンプルコードです。「この形では動作しない」というのを示す 011-generator-coroutine-modularize-failed.php 以外は実行可能なよう必要箇所を補完しています。たとえばイメージをつかむため、記事中で詳細へ触れていない多段の `yield` による部品化、`Fiber` を使ったイベントループについても簡易な実装を入れるなど、記事中で省略した箇所も一部補完しています。後半の例はイベントループを抜けてスクリプトを終了する手段を提供していないため、kill するなどして強制終了する必要があります。 

# 目次
- 137 ページ「PHPによるノンブロッキングI/O」の例示コード 1 → [001-blocking-io.php](./001-blocking-io.php)
- 138 ページ「PHPによるノンブロッキングI/O」の例示コード 2 → [002-non-blocking-io.php](./002-non-blocking-io.php)
- 138 ページ「I/O多重化」の `stream_select()` の例示コード → [003-stream-select.php](./003-stream-select.php)
- 138 〜 139 ページ「イベントループ」の例示コード → [004-eventloop.php](./004-eventloop.php)
- 139 ページ「コールバック（地獄）」のコールバックによる非同期処理の例示コード → [005-callback.php](./005-callback.php)
- 139 ページ「コールバック（地獄）」のコールバックのネストの例示コード → [006-callback-hell.php](./006-callback-hell.php)
- 140 ページ「Promise」の `Promise` 定義の例示コード → [007-promise.php](./007-promise.php)
- 140 ページ「Promise」の `Promise` 利用の例示コード → [008-promise-wait-and-echo.php](./008-promise-wait-and-echo.php)
- 141 ページ「ジェネレータとは」の例示コード → [009-generator.php](./009-generator.php)
- 141 ページ「ジェネレータによるコルーチン」の例示コード → [010-generator-coroutine.php](./010-generator-coroutine.php)
  - イベントループ修正部分と利用例の部分の 2 つ分を合わせてあります
- 141 ページ「ジェネレータによるコルーチンの部品化」の（うまく動かない状態の）例示コード → [011-generator-coroutine-modularize-failed.php](./011-generator-coroutine-modularize-failed.php)
- 142 ページ「ジェネレータによるコルーチンの部品化」の `yield from` を使う場合の例示コード → [012-generator-coroutine-modularize-yield-from.php](./012-generator-coroutine-modularize-yield-from.php)
- 142 ページ「ジェネレータによるコルーチンの部品化」の `yield` を使う場合の例示コード → [013-generator-coroutine-modularize-yield.php](./013-generator-coroutine-modularize-yield.php)
- 143 ページ「Fiberによるコルーチン」の `Fiber` の解説用の例示コード → [014-fiber.php](./014-fiber.php)
- 143 ページ「Fiberによるコルーチン」の `Fiber` による非同期処理の例示コード → [015-fiber-coroutine.php](./015-fiber-coroutine.php)

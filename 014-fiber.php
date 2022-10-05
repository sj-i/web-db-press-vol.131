<?php

$fiber = new Fiber(function (): void {
    $f = function (): void { // voidでよい
        // suspendで処理を中断
        // 直近で開始or再開した位置へ直接戻れる
        $received = Fiber::suspend('from fiber');
        echo $received, PHP_EOL;
    };
  $f();
});
// startでFiberの処理を開始
// 中断されるといったん処理が返ってくる
$from_fiber = $fiber->start();
echo $from_fiber, PHP_EOL;
// resumeで中断した位置から再開できる
$fiber->resume('to fiber');

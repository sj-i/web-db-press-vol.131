<?php

$descriptorspec = [0 => ['pipe', 'w'], 1 => ['pipe', 'w']];
$procs = [];
$procs[] = proc_open([PHP_BINARY, '-r', 'sleep(3); echo "a";'], $descriptorspec, $pipes1);
$procs[] = proc_open([PHP_BINARY, '-r', 'sleep(2); echo "b";'], $descriptorspec, $pipes2);
$procs[] = proc_open([PHP_BINARY, '-r', 'sleep(1); echo "c";'], $descriptorspec, $pipes3);
$stream1 = $pipes1[1];
$stream2 = $pipes2[1];
$stream3 = $pipes3[1];
$counter = 0;

$read = [$stream1, $stream2, $stream3];
$write = $except = null;
stream_select($read, $write, $except, null);
// 参照渡しで読み込み可能になったもののみ$readに残る
foreach ($read as $stream) {
    echo fgets($stream); // 最初に読み込み可能となる c だけが出るはず
}

foreach ($procs as $proc) {
    proc_close($proc);
}
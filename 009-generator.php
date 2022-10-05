<?php

function gen() {
    yield 1;
    yield 2;
    yield 3;
}
// 1, 2, 3の順で値を取り出せる
foreach (gen() as $yielded) {
    echo $yielded;
}

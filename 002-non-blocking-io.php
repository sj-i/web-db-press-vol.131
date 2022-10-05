<?php
$stream = STDIN;

// ストリームをノンブロッキングモードに変更
stream_set_blocking($stream, false);
$result = fgets($stream); // ブロックしない

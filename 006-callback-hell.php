<?php

// 解説用イベントループの実装
final class Loop {
    private \SplQueue $queue;
    private array $onReadable = [];
    private array $onReadableHandlers = [];

    private function __construct() {
        $this->queue = new \SplQueue();
    }
    /** シングルトン */
    public static function get(): self {
        static $self;
        return $self ??= new self();
    }
    /** リソース読み込み可能時のハンドラ登録 */
    public function onReadable(
        $resource,
        \Closure $closure
    ) {
        $id = (int)$resource;
        $this->onReadable[$id] = $resource;
        $this->onReadableHandlers[$id] = $closure;
    }
    /** ループの実行 */
    public function run(?\Closure $closure = null) {
        if (!is_null($closure)) {
            $this->queue->enqueue($closure);
        }
        while (!$this->shouldStop()) {
            $this->tick();
        }
    }
    private function tick(): void {
        // 解説用に読み込みのみ対応
        if (count($this->onReadable)) {
            $null = null;
            $readable = $this->onReadable;
            $selected = \stream_select(
                $readable,
                $null,
                $null,
                $this->shouldNotBlock() ? 0 : null
            );
            // 読み込み可能なものだけが残る
            foreach ($readable as $id => $stream) {
                $handler = $this->onReadableHandlers[$id];
                // イベントの処理をスケジュール
                $this->queue->enqueue(
                    function () use ($handler, $stream, $id) {
                        if (feof($stream)) {
                            unset($this->onReadable[$id]);
                            unset($this->onReadableHandlers[$id]);
                        }
                        return $handler($stream);
                    }
                );
            }
        }
        // スケジュールされた処理の実行
        while (!$this->queue->isEmpty()) {
            $this->queue->dequeue()();
        }
    }
    private function shouldNotBlock(): bool {
        // スケジュールされた処理があるときは
        // selectでブロックしないようにする
        return !$this->queue->isEmpty();
    }
    private function shouldStop(): bool {
        // やることがなければループ終了
        return $this->queue->isEmpty()
            and count($this->onReadable) === 0;
    }
}
stream_set_blocking(STDIN, false);

Loop::get()->onReadable(STDIN, function ($fp) {
    echo '1 ' . fgets($fp);
    Loop::get()->onReadable(STDIN, function ($fp) {
        echo '2 ' . fgets($fp);
        Loop::get()->onReadable(STDIN, function ($fp) {
            echo '3 ' . fgets($fp);
        });
    });
});

Loop::get()->run();

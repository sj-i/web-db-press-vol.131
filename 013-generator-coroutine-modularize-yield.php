<?php

// 解説用イベントループの実装
final class Loop {
    private \SplQueue $queue;
    private array $onReadable = [];
    private array $onReadableHandlers = [];
    private \WeakMap $chain;

    private function __construct() {
        $this->queue = new \SplQueue();
        $this->chain = new \WeakMap();
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
            $result = $this->queue->dequeue()();
            if ($result instanceof \Generator) {
                // まず最初のyieldまで進める
                $yielded = $result->current();
                if ($yielded instanceof \Promise) {
                    // Promiseの完了時に次のyieldまで進める
                    $step = function ($value) use ($result, &$step) {
                        $yielded = $result->send($value);
                        if ($yielded instanceof \Promise) {
                            // 次もPromiseがyieldされたなら更にその完了時にその次のyieldまで進める
                            $yielded->then($step);
                        } else {
                            $this->handleNestedGenerator($result, $yielded);
                        }
                    };
                    $yielded->then($step);
                } else {
                    $this->handleNestedGenerator($result, $yielded);
                }
            }
        }
    }
    private function handleNestedGenerator($generator, $yielded): void {
        if ($yielded instanceof \Generator) {
            // Generatorがyieldされたなら自身の呼び出し元を保存し実行をスケジュール
            $this->chain[$yielded] = $generator;
            $this->queue->enqueue(fn () => $yielded);
        } elseif (isset($this->chain[$generator])) {
            // GeneratorかPromiseしかyieldされないとするとここに来るのは完了時
            // 保存していた呼び出し元を取り出し次へ進めて実行をスケジュール
            $caller = $this->chain[$generator];
            unset($this->chain[$generator]);
            $caller->send($yielded);
            $this->queue->enqueue(fn () => $caller);
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

// 解説用の極めて簡易的な実装、本当はもう少し複雑
class Promise {
    private array $thens = [];
    public function then(\Closure $then) {
        $this->thens[] = $then;
        return $this;
    }
    public function resolve(mixed $value) {
        while (!empty($this->thens)) {
            $result = array_shift($this->thens)($value);
            if ($result instanceof Promise) {
                $result->thens = [
                    ...$result->thens,
                    ...$this->thens
                ];
                $this->thens = [];
            }
        }
    }
}

stream_set_blocking(STDIN, false);

function waitAndEcho(string|int $prefix): Promise {
    $promise = new Promise();
    $promise->then(function () use ($prefix) {
        echo $prefix . ' ' . fgets(STDIN);
    });
    Loop::get()->onReadable(
        STDIN, fn () => $promise->resolve(null)
    );
    return $promise;
}

function subRoutine1(): \Generator {
    yield waitAndEcho(1);
}
function subRoutine2(): \Generator {
    yield waitAndEcho(2);
    yield waitAndEcho(3);
}

// イベントループ側の実装しだいでは可能になる
Loop::get()->run(function () {
    yield subRoutine1();
    yield subRoutine2();
});

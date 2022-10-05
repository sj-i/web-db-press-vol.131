<?php

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

<?php
namespace Module;

/**
 * Interface CacheInterface
 */
interface CacheInterface
{
    /**
     * 파일에 캐시 쓰기
     *
     * @param resource $filePointer
     */
    public function cacheWrite(resource $filePointer) : void;
}
<?php
/**
 * Stream binary files with high-performance HTTP 206 Partial Content Range support.
 * Serves requested slices with fixed-memory stream loops, keeping constant server RAM footprint.
 */
function stream_file_ranged($file, $contentType = 'application/pdf', $isPrivate = true, $cacheMaxAge = 300) {
    if (!is_file($file)) {
        http_response_code(404);
        exit('File not found.');
    }
    
    // Release PHP session lock immediately so concurrent Range requests run in parallel with zero latency
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    
    // Clear all active output buffers to allow immediate chunk streaming
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Prevent script execution timeout on large file streams
    @set_time_limit(0);
    
    $size = filesize($file);
    $mtime = filemtime($file);
    
    // High-performance ETag based on mtime + size (O(1) instant lookup without reading entire file)
    $etag = sprintf('"%x-%x"', $mtime, $size);
    $lastMod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    
    // Handle conditional GET (304 Not Modified)
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
        http_response_code(304);
        exit;
    }
    
    // Parse Range header
    $rangeHeader = $_SERVER['HTTP_RANGE'] ?? null;
    $start = 0;
    $end = $size - 1;
    
    if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $m)) {
        $start = $m[1] !== '' ? (int)$m[1] : $size - (int)$m[2];
        $end   = $m[2] !== '' ? min((int)$m[2], $size - 1) : $size - 1;
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    } else {
        http_response_code(200);
    }
    
    $length = $end - $start + 1;
    
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . $length);
    header('Accept-Ranges: bytes');
    header('ETag: ' . $etag);
    header('Last-Modified: ' . $lastMod);
    
    if ($isPrivate) {
        header('Cache-Control: private, max-age=' . $cacheMaxAge . ', must-revalidate');
    } else {
        header('Cache-Control: public, max-age=' . $cacheMaxAge . ', must-revalidate');
    }
    
    header('Content-Disposition: inline');
    header('X-Content-Type-Options: nosniff');
    
    // Stream requested byte range in optimal 256 KB chunks
    $fp = fopen($file, 'rb');
    if ($fp) {
        fseek($fp, $start);
        $remaining = $length;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = min(262144, $remaining); // 256 KB chunks — 4x fewer round-trips
            echo fread($fp, $chunk);
            flush();
            $remaining -= $chunk;
            if (connection_aborted()) break;
        }
        fclose($fp);
    }
    exit;
}

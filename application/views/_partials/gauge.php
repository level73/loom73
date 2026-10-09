<?php
$requests = [];

if ($postGauge !== null):
    $requests['Previous POST'] = $postGauge;
endif;

$requests['Current request'] = $gauge;
?>
<aside class="gauge" aria-label="Gauge request metrics">
    <details>
        <summary>
            Gauge · <?php if ($postGauge !== null): ?>POST → <?php endif; ?><?php echo htmlspecialchars(
                    $gauge['method'] . ' ' . $gauge['path'],
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
            ); ?>
        </summary>

        <?php foreach ($requests as $label => $metrics): ?>
            <section class="gauge-request">
                <?php if ($postGauge !== null): ?>
                    <h2 class="gauge-request-heading">
                        <?php echo htmlspecialchars(
                                $label . ' · ' . $metrics['method'] . ' ' . $metrics['path'],
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                        ); ?>
                    </h2>
                <?php endif; ?>

                <dl>
                    <div><dt>Elapsed</dt><dd><?php echo number_format($metrics['request_ms'], 2); ?> ms</dd></div>
                    <div><dt>Memory</dt><dd><?php echo number_format($metrics['memory_bytes'] / 1048576, 1); ?> MiB</dd></div>
                    <div><dt>Peak memory</dt><dd><?php echo number_format($metrics['peak_memory_bytes'] / 1048576, 1); ?> MiB</dd></div>
                    <div><dt>DB queries</dt><dd><?php echo (int) $metrics['query_count']; ?></dd></div>
                    <div><dt>DB execution</dt><dd><?php echo number_format($metrics['query_ms'], 2); ?> ms</dd></div>
                    <div><dt>PHP</dt><dd><?php echo htmlspecialchars($metrics['php_version'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt>HTTP</dt><dd><?php echo (int) $metrics['http_status']; ?></dd></div>
                </dl>
            </section>
        <?php endforeach; ?>

    </details>
</aside>

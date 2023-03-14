
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ?></title>
    <?php
        echo $this->Html->meta('icon');
        echo $this->Html->script('https://polyfill.io/v3/polyfill.min.js?features=ResizeObserver');
        echo $this->Html->script('https://code.jquery.com/jquery-3.6.4.js');
        echo $this->Html->script('https://cdn.jsdelivr.net/npm/chart.js');
        echo $this->Html->script('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');
        echo $this->Html->css('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
    ?>
</head>
<body>
    <main class="main">
        <div class="container">
            <?= $this->fetch('content') ?>
        </div>
    </main>
</body>
</html>

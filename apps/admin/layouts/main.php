<!DOCTYPE html>
<html dir="ltr" lang="en" class="no-js">
    <?php $this->include_template('layout/head'); ?>
    <body>
        <div id="header">
            <?php $this->include_template('layout/header');
            $this->include_template('layout/nav'); ?>
        </div>
        <?php $this->include_template($this->template);
        $this->include_template('layout/footer');
        $this->include_template('parts/scrollup'); ?>
    </body>
</html>
<?php

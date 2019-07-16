<!DOCTYPE html>
<html dir="ltr" lang="en" class="no-js">
    <?php $this->include_template('layout/head'); ?>
    <body>
        <div id="header">
            <?php $this->include_template('layout/header'); ?>
        </div>
        <?php $this->include_template($this->template);
        $this->include_template('layout/footer'); ?>
    </body>
</html>
<?php

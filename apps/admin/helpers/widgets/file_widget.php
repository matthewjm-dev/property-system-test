<?php // Files Widget

ipsCore::requires_model('file');

class file_widget extends dashboard_widget
{
    protected $widget_title = 'Files';

    public function build()
    {
        $this->load_model('admin_file', 'file');

        $files = $this->file->get_all();
        $count_all = count($files);
        $count_files = 0;
        $count_images = 0;

        foreach ($files as $file) {
            if ($file->type == 'image') {
                $count_images++;
            } else {
                $count_files++;
            }
        }

        $this->add_widget_data([
            'url' => '/admin/files/',
            'title' => 'File Stats',
            'icon' => 'far fa-file-image',
            'count_all' => $count_all . ' Total',
            'count_files' => $count_files . " Files",
            'count_images' => $count_images . " Images",
        ]);
    }

}

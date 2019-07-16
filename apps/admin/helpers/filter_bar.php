<?php // Module Filters

ipsCore::requires_core_helper(['form_builder']);

class filter_bar extends ipsCore_helper
{

    protected $module_name;
    protected $submit_location = false;
    protected $current_page = 1;

    protected $filter_orderby = 'position';
    protected $filter_order = 'DESC';
    protected $filter_perpage = '10';
    protected $filter_search = '';

    // Getters
    public function get_current_page()
    {
        return $this->current_page;
    }
    public function get_filter_orderby()
    {
        return $this->filter_orderby;
    }

    public function get_filter_order()
    {
        return $this->filter_order;
    }

    public function get_filter_perpage()
    {
        return $this->filter_perpage;
    }

    public function get_filter_search()
    {
        return $this->filter_search;
    }

    // Construct

    public function __construct($name, $current_page = false, $submit_location = false)
    {
        $this->module_name = $name;
        if ($current_page) {
            $this->current_page = $current_page;
        }

        parent::__construct($name);

        $this->load_filters();

        if ($submit_location) {
            $this->submit_location = $submit_location . '/' . $name;
            $this->build_filterbar();
            $this->render();
        }
    }

    // Functions

    public function load_filters()
    {
        $items = ['filter_order', 'filter_perpage', 'filter_search', 'current_page'];

        if (isset($_POST) && !empty($_POST)) {
            foreach ($items as $item) {
                if (isset($_POST[$item])) {
                    if ($item == 'filter_order') {
                        $order_ex = explode(' ', $_POST[$item]);
                        $order = (isset($order_ex[0]) ? $order_ex[0] : $this->get_filter_order() );
                        $orderby = (isset($order_ex[1]) ? $order_ex[1] : $this->get_filter_orderby() );;
                        ipsCore::$session->write($this->module_name . '_filter_orderby', $order);
                        ipsCore::$session->write($this->module_name . '_filter_order', $orderby);
                    } else {
                        ipsCore::$session->write($this->module_name . '_' . $item, $_POST[$item]);
                    }
                }
            }
        }

        $items[] = 'filter_orderby';

        foreach ($items as $item) {
            $this->$item = (ipsCore::$session->read($this->module_name . '_' . $item) ?: $this->$item);
        }
    }

    public function build_filterbar()
    {
        $form = new ipsCore_form_builder('filterbar');
        $form->set_action($this->submit_location);
        $form->set_classes(['ajax_form', 'filterbar']);

        $form->add_select('filter_order', 'Order', ['value' => $this->get_filter_orderby() . ' ' . $this->get_filter_order(), 'options' => $this->get_order_options(), 'required' => false, 'placeholder' => 'Choose Sort Order']);
        $form->add_select('filter_perpage', 'Show Per Page', ['value' => $this->get_filter_perpage(), 'options' => $this->get_perpage_options(), 'required' => false, 'placeholder' => 'Choose Per Page']);
        $form->add_text('filter_search', 'Search', ['value' => $this->get_filter_search(), 'required' => false, 'placeholder' => 'Enter search term']);
        $form->add_hidden('current_page', ['value' => $this->get_current_page(), 'required' => false]);

        return $form->render();
    }

    public function get_order_options()
    {
        return [
            ['value' => 'position ASC', 'text' => 'Position Ascending'],
            ['value' => 'position DESC', 'text' => 'Position Descending'],
            ['value' => 'created ASC', 'text' => 'Date Created Ascending'],
            ['value' => 'created DESC', 'text' => 'Date Created Descending'],
        ];
    }

    public function get_perpage_options()
    {
        return [
            ['value' => '1', 'text' => '1 Per Page'],
            ['value' => '5', 'text' => '5 Per Page'],
            ['value' => '10', 'text' => '10 Per Page'],
            ['value' => '15', 'text' => '15 Per Page'],
            ['value' => '20', 'text' => '20 Per Page'],
            ['value' => 'all', 'text' => 'All Items'],
        ];
    }

    public function render()
    {
        $data = [
            'module_name' => $this->module_name,
            'filterbar_form' => $this->build_filterbar(),
        ];

        $this->add_data(['filterbar' => ipsCore::get_part('parts/filterbar', $data)]);
    }

}

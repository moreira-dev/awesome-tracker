<?php

defined('ABSPATH') or die();

class AwesomeTrackerLogTable extends WP_AwesomeTracker_Table {

    /**
     * Contains the SQL for retrieving all records
     *
     * @var string
     */
    public $sqlNoFilters = '';

    /**
     * Contains the final SQL for this prepared query
     *
     * @var string
     */
    public $sql = '';

    /**
     * Contains the SQL for the where part
     *
     * @var string
     */
    public $sqlWhere = '';

    /**
     * Contains the SQL to count the total number of records filtered
     *
     * @var string
     */
    public $sqlCount = '';

    /**
     * Field to order by
     *
     * @var string
     */
    public $orderby = '';

    /**
     * @var string
     */
    public $orderway = 'DESC';

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var int
     */
    public $perpage = 0;

    /**
     * Filter to use to get the SQL column value
     *
     * @var array
     */
    public $columnToQuery = array(
        'type' => false,
        'details' => false,
        'username' => 'u.display_name',
        'ip' => 'v.ip',
        'date' => 'v.visited'
    );

    public $defaultOrderBy = 'date';

    public $defaultOrderWay = 'DESC';

    function __construct($array = array()) {

        if (empty($array))
            $array = array(
                'singular' => 'record',     //singular name of the listed records
                'plural' => 'records',    //plural name of the listed records
                'ajax' => false        //does this table support ajax?
            );

        //Set parent defaults
        parent::__construct($array);

    }

    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'type':
            case 'username':
            case 'details':
            case 'ip':
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_type($item) {

        $actions = array(
            'view' => sprintf(
                    '<a href="?page=%s&action=%s&record=%d">' . __('View', AwesomeTracker::TEXT_DOMAIN) . '</a>',
                    esc_attr($_REQUEST['page']),
                    'view',
                    $item['ID']
            )
        );

        return sprintf('%1$s %2$s',
                       sprintf(
                               '<a href="?page=%s&action=%s&record=%d">' . $item['type'] . '</a>',
                               esc_attr($_REQUEST['page']),
                               'view',
                               $item['ID']
                       ),
                       $this->row_actions($actions)
        );

    }

    function column_username($item) {

        if (!$item['user_id'])
            return '';

        return '<a href="' . get_edit_user_link($item['user_id']) . '" target="_blank">' . $item['username'] . '<br/>(' . $item['user_login'] . ')</a>';
    }


    function get_columns() {

        $columns = array(
            'type' => 'Type',
            'details' => 'Details',
            'username' => 'User',
            'ip' => 'IP',
            'date' => 'Date'
        );

        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'username' => array('username', false),
            'ip' => array('ip', false),
            'date' => array('date', false)
        );

        return $sortable_columns;
    }

    function get_bulk_actions() {

        $actions = array();

        return $actions;
    }

    function get_orderby($orderby = ''){

        if($orderby)
            foreach ($this->columnToQuery as $columnKey => $columnSQL)
                if($orderby == $columnKey && $columnSQL)
                    return $columnSQL;


        return $this->columnToQuery[$this->defaultOrderBy];
    }

    function get_orderway($orderway = ''){

        if(!$orderway)
            return $this->defaultOrderWay;

        $orderway = strtoupper($orderway);

        if(in_array($orderway,array('ASC','DESC')))
            return $orderway;

        return $this->defaultOrderWay;
    }

    function prepare_sql_filters() {

        global $wpdb;

        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $option, true);

        if (empty ($per_page) || $per_page < 1)
            $per_page = $screen->get_option('per_page', 'default');

        $orderby = $this->get_orderby($_REQUEST['orderby']);

        $order = $this->get_orderway($_REQUEST['order']);

        $search = (!empty($_REQUEST['s'])) ? $_REQUEST['s'] : '';
        $filter_search = "";


        if (!empty($search)) {
            $filter_search = $wpdb->prepare(' AND (u.display_name LIKE "%1$s" 
                OR p.post_title LIKE "%1$s"
                OR t.name LIKE "%1$s"  
                OR v.taxonomy LIKE "%1$s" 
                OR v.archive_ptype LIKE "%1$s" 
                OR v.api_route LIKE "%1$s" 
                OR v.query_404 LIKE "%1$s" 
                OR v.search_query LIKE "%1$s" 
                OR v.ip LIKE "%1$s" 
                OR v.visited LIKE "%1$s" 
                ) ', '%' . $wpdb->esc_like($search) . '%');
        }

        $filter = (isset($_REQUEST['filtering']) ? $_REQUEST['filtering'] : 'all');
        $where_filter = $this->get_filter_where($filter);

        if (isset($_REQUEST['at_users_filter'])
            && is_numeric($_REQUEST['at_users_filter'])
            && $_REQUEST['at_users_filter'] > 0) {

            $where_filter .= " AND v.user_id = '{$_REQUEST['at_users_filter']}' ";
        }


        $current_page = $this->get_pagenum();


        $offset = $per_page * ($current_page - 1);

        $this->sqlWhere = $where_filter . ' ' . $filter_search;
        $this->orderby = $orderby;
        $this->orderway = $order;
        $this->offset = $offset;
        $this->perpage = $per_page;

    }

    function buildSql() {

        $this->prepare_sql_filters();

        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $this->sqlNoFilters = "SELECT p.post_title, t.name as term_name, v.*, u.display_name as username, u.user_login, au.display_name as author
                FROM {$tableTrack} as v
                LEFT JOIN {$wpdb->posts} as p ON (p.ID = v.post_id)
                LEFT JOIN {$wpdb->terms} as t ON (t.term_id = v.term_id)
                LEFT JOIN {$wpdb->users} as u ON (u.ID = v.user_id)
                LEFT JOIN {$wpdb->users} as au ON (au.ID = v.author_archive)
                WHERE 1=1 ";

        $this->sql = $this->sqlNoFilters . $this->sqlWhere;

        if ($this->orderby)
            $this->sql .= " ORDER BY " . $this->orderby . ' ' . $this->orderway;


        if ($this->offset || $this->perpage)
            $this->sql .= " LIMIT " . $this->offset . " , " . $this->perpage;

        $this->sqlCount = "SELECT COUNT(*) FROM (" . $this->sqlNoFilters . $this->sqlWhere . ") as temp";

    }


    function prepare_items() {

        global $wpdb;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->buildSql();

        $records = $wpdb->get_results($this->sql);

        $this->items = array();

        if (is_array($records) && !empty($records))
            foreach ($records as $record) {
                $record = new AwesomeTracker_Record($record);

                $this->items[] = array(
                    'ID' => $record->ID,
                    'type' => $record->label,
                    'details' => nl2br($record->description),
                    'username' => $record->username,
                    'user_login' => $record->user_login,
                    'user_id' => $record->user_id,
                    'post_id' => $record->post_id,
                    'date' => $record->visited_formatted,
                    'ip' => $record->ip
                );
            }


        $total_items = $wpdb->get_var($this->sqlCount);

        $this->set_pagination_args(array(
                                       'total_items' => $total_items,
                                       'per_page' => $this->perpage,
                                       'total_pages' => ceil($total_items / $this->perpage)
                                   ));
    }

    function get_views() {

        global $wpdb;

        $views = array();
        $current = (!empty($_REQUEST['filtering']) ? $_REQUEST['filtering'] : 'all');


        $filters = array(
            'all' => __('All records', AwesomeTracker::TEXT_DOMAIN),
            'home' => __('Home records', AwesomeTracker::TEXT_DOMAIN),
            '404' => __('404 records', AwesomeTracker::TEXT_DOMAIN),
            'search_query' => __('Search page records', AwesomeTracker::TEXT_DOMAIN),
            'archive' => __('Archive records', AwesomeTracker::TEXT_DOMAIN),
            'post' => __('Post / Page records', AwesomeTracker::TEXT_DOMAIN),
            'api' => __('API records', AwesomeTracker::TEXT_DOMAIN),

        );

        foreach ($filters as $key => $value) {

            $where_filter = $this->get_filter_where($key);

            $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

            $sql = "SELECT COUNT(v.ID)
                FROM {$tableTrack} v
                WHERE 1=1 $where_filter";

            $count = $wpdb->get_var($sql);

            $class = ($current == $key ? ' class="current"' : '');
            $url = remove_query_arg('filtering');
            $url = add_query_arg('filtering', $key, $url);
            $views[$key] = "<a href='{$url}' {$class} >{$value} ({$count})</a>";
        }

        return $views;
    }

    private function get_filter_where($filterKey) {

        $where_filter = "";
        switch ($filterKey) {
            case '404':
                $where_filter = " AND query_404 IS NOT NULL ";
                break;
            case 'search_query':
                $where_filter = " AND search_query IS NOT NULL ";
                break;
            case 'archive':
                $where_filter = " AND (
                                        archive_ptype IS NOT NULL 
                                            OR date_archive IS NOT NULL 
                                            OR author_archive IS NOT NULL
                                            OR v.term_id IS NOT NULL
                                      ) ";
                break;
            case 'home':
                $where_filter = " AND is_home > 0 ";
                break;
            case 'post':
                $where_filter = " AND v.post_id IS NOT NULL ";
                break;
            case 'api':
                $where_filter = " AND v.api_route IS NOT NULL ";
                break;
        }

        return $where_filter;
    }


    function restrict_manage_posts() {

        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $sql = "SELECT u.ID, u.display_name, u.user_login 
                        FROM {$wpdb->users} u 
                        WHERE u.ID IN (SELECT DISTINCT(user_id) FROM {$tableTrack}) 
                        ORDER BY display_name";
        $users = $wpdb->get_results($sql, ARRAY_A);

        ?>

        <button type="submit" name="at_csv_export" class="button csv-export" value="1">
            <span class="dashicons dashicons-download"> </span>
            <?php _e('Export current results', AwesomeTracker::TEXT_DOMAIN); ?>
        </button>
        <?php if(!empty($users)) { ?>
            <div class="at_users_filter_wrap">
                <select name="at_users_filter" class="at_chosen_user">
                    <option value=""><?php _e('Filter by user', AwesomeTracker::TEXT_DOMAIN); ?></option>
                    <?php

                    $current_v = isset($_REQUEST['at_users_filter']) ? $_REQUEST['at_users_filter'] : '';
                    foreach ($users as $value) {
                        printf
                        (
                            '<option value="%s"%s>%s</option>',
                            $value['ID'],
                            $value['ID'] == $current_v ? ' selected="selected"' : '',
                            $value['display_name'] . ' (' . $value['user_login'] . ')'
                        );
                    }
                    ?>
                </select>
            </div>
            <?php

        }

    }

    protected function bulk_actions($which = '') {

        parent::bulk_actions();
        $this->restrict_manage_posts();
    }

}

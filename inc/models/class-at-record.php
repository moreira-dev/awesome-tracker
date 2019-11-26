<?php

/**
 * Class to implement a tracked record from DB
 */
class AwesomeTracker_Record {

    /**
     * Record ID.
     *
     * @var int
     */
    public $ID;

    /**
     * ID of the visited post.
     *
     * @var int
     */
    public $post_id = 0;

    /**
     * ID of the visited term.
     *
     * @var int
     */
    public $term_id = 0;

    /**
     * ID of the user making the visit.
     *
     * @var int
     */
    public $user_id = 0;

    /**
     * Post type name of the visited archive
     *
     * @var string
     */
    public $archive_ptype = '';

    /**
     * Taxonomy name of the visited archive
     *
     * @var string
     */
    public $taxonomy = '';

    /**
     * Requested date for a date archive
     *
     * @var string
     */
    public $date_archive = '';

    /**
     * Requested author for an author archive
     *
     * @var string
     */
    public $author_archive = '';

    /**
     * Time where the visit was made
     *
     * @var string
     */
    public $visited = '0000-00-00 00:00:00';

    /**
     * Formatted Time where the visit was made
     *
     * @var string
     */
    public $visited_formatted = '';

    /**
     * Requested query url when the 404 error happened
     *
     * @var string
     */
    public $query_404 = '';

    /**
     * Requested search parameters for a search query
     *
     * @var string
     */
    public $search_query = '';

    /**
     * Wheter the visit was to the homepage or not
     *
     * @var bool
     */
    public $is_home = false;

    /**
     * Pagination number for an archive
     *
     * @var int
     */
    public $page = 0;

    /**
     * IP of the client
     *
     * @var string
     */
    public $ip = '';

    /**
     * Country code for the IP
     *
     * @var string
     */
    public $country_code = '';

    /**
     * Country name for the IP
     *
     * @var string
     */
    public $country_name = '';

    /**
     * @var string
     */
    public $term_name = '';

    /**
     * @var string
     */
    public $author = '';

    /**
     * @var string
     */
    public $post_title = '';

    /**
     * Username of the user that was tracked
     *
     * @var string
     */
    public $username = '';

    /**
     * User login of the user that was tracked
     *
     * @var string
     */
    public $user_login = '';

    /**
     * Label in a human readable form for this type of record
     *
     * @var string
     */
    public $label = '';

    /**
     * More detailed information for the label
     *
     * @var string
     */
    public $description = '';

    /**
     * Route path of the tracked API call
     *
     * @var string
     */
    public $api_route = '';

    /**
     * Method used on the API call
     *
     * @var string
     */
    public $api_method = '';

    /**
     * Whether this record is for a 404 page or not
     *
     * @var bool
     */
    public $is_404 = false;

    /**
     * Whether this record is for a search query or not
     *
     * @var bool
     */
    public $is_search = false;

    /**
     * Whether this record is for an archive page or not
     *
     * @var bool
     */
    public $is_archive = false;

    /**
     * Whether this record is for a post page or not
     *
     * @var bool
     */
    public $is_post = false;

    /**
     * Whether this record is for an API call or not
     *
     * @var bool
     */
    public $is_api = false;

    /**
     * Post / Page for this post record
     *
     * @var null|WP_Post
     */
    public $post = null;

    /**
     * Term for this archive record
     *
     * @var null|WP_Term
     */
    public $term = null;


    /**
     * Constructor.
     *
     * @param int|object|array|null $record Record ID or record from DB.
     */
    public function __construct($record = null) {

        if (is_object($record) || is_array($record)) {

            $this->set_vars($record);

        }
        elseif (is_numeric($record)) {

            $this->fill_object_from_db($record);

        }

    }

    private function fill_object_from_db($ID) {

        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;
        $vars = $wpdb->get_row($wpdb->prepare(
            "SELECT p.post_title, t.name as term_name, v.*, 
                          u.display_name as username, u.user_login, au.display_name as author
                FROM {$tableTrack} as v
                LEFT JOIN {$wpdb->posts} as p ON (p.ID = v.post_id)
                LEFT JOIN {$wpdb->terms} as t ON (t.term_id = v.term_id)
                LEFT JOIN {$wpdb->users} as u ON (u.ID = v.user_id)
                LEFT JOIN {$wpdb->users} as au ON (au.ID = v.author_archive)
                WHERE v.ID = %d LIMIT 1", $ID));
        $this->set_vars($vars);
    }

    /**
     * @param bool $refresh Whether to retrieve the newly updated record from the DB
     *
     * @return bool
     */
    public function save($refresh = false) {

        $update = false;

        if (!empty($this->ID) && is_numeric($this->ID))
            $update = true;

        global $wpdb;

        $table = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        if ($this->emptyTime($this->visited)) {
            $this->visited = current_time('mysql');
        }

        if (empty($this->ip)) {
            $this->ip = AwesomeTrackerHelper::get_client_ip();
        }

        $data = array(
            'user_id' => AwesomeTrackerHelper::get_null_if_empty($this->user_id),
            'ip' => AwesomeTrackerHelper::limit_string_to($this->ip, 45),
            'visited' => $this->visited,
            'page' => $this->page,
            'is_home' => $this->is_home
        );

        $format = array('%d', '%s', '%s', '%d', '%d');

        $data['term_id'] = AwesomeTrackerHelper::get_null_if_empty($this->term_id);
        $format[] = '%d';
        $data['taxonomy'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->taxonomy, 32));
        $format[] = '%s';
        $data['post_id'] = AwesomeTrackerHelper::get_null_if_empty($this->post_id);
        $format[] = '%d';
        $data['archive_ptype'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->archive_ptype, 45));
        $format[] = '%s';
        $data['query_404'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->query_404, 150));
        $format[] = '%s';
        $data['search_query'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->search_query, 100));
        $format[] = '%s';
        $data['author_archive'] = AwesomeTrackerHelper::get_null_if_empty($this->author_archive);
        $format[] = '%d';
        $data['date_archive'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->date_archive, 45));
        $format[] = '%s';
        $data['api_route'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->api_route, 150));
        $format[] = '%s';
        $data['api_method'] = AwesomeTrackerHelper::get_null_if_empty(AwesomeTrackerHelper::limit_string_to($this->api_method, 50));
        $format[] = '%s';

        if ($update) {
            $done = $wpdb->update(
                $table,
                $data,
                array('ID' => $this->ID),
                $format,
                array('%d')
            );
        }
        else {
            $done = $wpdb->insert(
                $table,
                $data,
                $format
            );
        }

        if (!$done)
            return false;

        $ID = $update ? $this->ID : $wpdb->insert_id;

        if ($refresh)
            $this->fill_object_from_db($ID);

        return true;

    }

    public static function get_label_and_description($record){
        $returnArray = array(
            'label' => '',
            'description' => ''
        );
        if ($record->api_route) {
            $returnArray['label'] = __('API Route View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Queried Route: %s ' . PHP_EOL . 'Method: %s', AwesomeTracker::TEXT_DOMAIN), $record->api_route, $record->api_method);
        }
        elseif ($record->query_404) {
            $returnArray['label'] = __('404 Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Failed query: %s', AwesomeTracker::TEXT_DOMAIN), $record->query_404);
        }
        elseif ($record->search_query) {
            $returnArray['label'] = __('Search Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Search parameters: %s', AwesomeTracker::TEXT_DOMAIN), $record->search_query);
        }
        elseif ($record->archive_ptype) {
            $returnArray['label'] = __('Archive Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Archive for Post Type: %s', AwesomeTracker::TEXT_DOMAIN), $record->archive_ptype);
        }
        elseif ($record->term_id) {
            $returnArray['label'] = __('Archive Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Archive for Term: %s', AwesomeTracker::TEXT_DOMAIN), $record->term_name);
        }
        elseif ($record->author_archive) {
            $returnArray['label'] = __('Archive Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Archive for Author: %s', AwesomeTracker::TEXT_DOMAIN), $record->author);
        }
        elseif ($record->date_archive) {
            $returnArray['label'] = __('Archive Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Archive for Date: %s', AwesomeTracker::TEXT_DOMAIN), $record->date_archive);
        }
        elseif ($record->is_home) {
            $returnArray['label'] = __('Home Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = $record->post_title ? $record->post_title : __('Posts index', AwesomeTracker::TEXT_DOMAIN);
        }
        else {
            $returnArray['label'] = __('Post / Page View', AwesomeTracker::TEXT_DOMAIN);
            $returnArray['description'] = sprintf(__('Post / Page title: %s', AwesomeTracker::TEXT_DOMAIN), $record->post_title);
        }

        return $returnArray;
    }


    private function set_type() {

        if ($this->api_route) {
            $this->is_api = true;
            $this->post = $this->post_id ? get_post($this->post_id) : null;
        }
        elseif ($this->query_404) {
            $this->is_404 = true;
        }
        elseif ($this->search_query) {
            $this->is_search = true;
        }
        elseif ($this->archive_ptype) {
            $this->is_archive = true;
        }
        elseif ($this->term_id) {
            $this->is_archive = true;
        }
        elseif ($this->author_archive) {
            $this->is_archive = true;
        }
        elseif ($this->date_archive) {
            $this->is_archive = true;
        }
        elseif ($this->is_home) {
            $this->post = $this->post_id ? get_post($this->post_id) : null;
        }
        else {
            $this->is_post = true;
            $this->post = $this->post_id ? get_post($this->post_id) : null;
        }

        $labelAndDescription = self::get_label_and_description($this);

        $this->label = $labelAndDescription['label'];
        $this->description = $labelAndDescription['description'];
    }

    private function set_formatted_date() {

        if ($this->emptyTime($this->visited))
            return null;

        $date = get_option('date_format');
        $time = get_option('time_format');
        $this->visited_formatted = date($date . ' ' . $time, strtotime($this->visited));
    }

    private function set_vars($object) {

        if (is_array($object))
            $object = (object)$object;

        foreach (get_object_vars($object) as $key => $value) {
            $this->$key = is_serialized($value) ? unserialize($value) : $value;
        }
        $this->set_formatted_date();
        $this->set_type();
    }

    private function emptyTime($stringTime) {

        return empty($stringTime) || $stringTime == '0000-00-00 00:00:00';
    }

    public static function delete_old_recordsDB($daysOld){

        $daysOld = intval($daysOld);
        if($daysOld <= 0)
            return true;

        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        $datetime = new DateTime( 'now', wp_timezone() );
        $datetime->modify("-{$daysOld} day");
        $dateToDelete = $datetime->format('Y-m-d H:i:s');

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$tableTrack} WHERE visited < %s",
                $dateToDelete
            )
        );
    }

    public static function delete_all_records(){
        global $wpdb;

        $tableTrack = $wpdb->prefix . AwesomeTracker::TBL_VISITS;

        return $wpdb->query("TRUNCATE TABLE $tableTrack");
    }

    /**
     * Convert object to array.
     *
     * @return array Object as array.
     */
    public function to_array() {

        return get_object_vars($this);
    }

}

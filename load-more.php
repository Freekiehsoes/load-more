<?php
/*
  Plugin Name: Easy load more posts
  Description: This plugin is used to create a load more functionality
  Author: Freek Attema
  Version: 1.1
 */

class LoadMore
{
    /** @var string */
    private $loadMoreSelector;
    /** @var string */
    private $outletSelector;

    /** @var string|string[] */
    private $postType;
    /** @var int */
    private $postsPerPage;
    /** @var int */
    private $page;
    /** @var array */
    private $args;

    /** @var null|callable */
    private $template = null;

    /**
     * Start collection of html
     */
    private static function startTemplate()
    {
        ob_start();
    }

    /**
     * End collection of html and return a string
     * @return false|string
     */
    private static function endTemplate()
    {
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * LoadMore constructor.
     * @param string|null|string[] $postType
     * @param null|int $postsPerPage
     * @param array $args
     * @param int $page
     */
    public function __construct($postType = null, $postsPerPage = null, $args = [], $page = 1)
    {
        if (is_array($args)) {
            $this->args = $args;
        } else {
            $this->args = [];
        }

        if (is_numeric($postsPerPage)) {
            $this->postsPerPage = $postsPerPage;
        } else {
            $this->postsPerPage = 6;
        }

        if (is_string($postType) || is_array($postType)) {
            $this->postType = $postType;
        } else {
            $this->postType = 'blog';
        }

        if (is_numeric($page)) {
            $this->page = $page;
        } else {
            $this->page = 1;
        }
    }

    /**
     * @return false|string
     */
    private function getFilledTemplate()
    {
        if (!$this->template)
            return '';

        self::startTemplate();
        call_user_func($this->template);
        return self::endTemplate();
    }

    /**
     * @return string
     */
    private function loadPosts(): string
    {
        if (!$this->template) {
            return "";
        }

        $arguments = array(
            'post_type' => $this->postType,
            'posts_per_page' => $this->postsPerPage,
            'paged' => $this->page,
        );


        $loop = new WP_Query(
            array_merge($this->args, $arguments)
        );

        $content = "";

        while ($loop->have_posts()) : $loop->the_post();
            $content .= $this->getFilledTemplate();
        endwhile;
        wp_reset_query();

        return esc_html(__($content));
    }

    /**
     * @param string $selector
     * @throws Exception
     */
    public function setLoadMoreSelector(string $selector)
    {
        if (is_string($selector)) {
            $this->loadMoreSelector = $selector;
        } else {
            throw new Exception('The selector should be a string');
        }
    }

    /**
     * @param string $selector
     * @throws Exception
     */
    public function setOutletSelector(string $selector)
    {
        if (is_string($selector)) {
            $this->outletSelector = $selector;
        } else {
            throw new Exception('The selector should be a string');
        }
    }

    /**
     * @param callable $template
     * @throws Exception
     */
    public function setTemplate(callable $template)
    {
        if (is_callable($template)) {
            $this->template = $template;
        } else {
            throw new Exception('Template should be a callable');
        }
    }

    public function init()
    {
        if (isset($_GET['postType'])) {
            $this->isRest();
            die();
        }

        $this->isNormal();
    }

    /**
     * Place the needed javascript
     */
    private function placeJs()
    {
        add_action('wp_enqueue_scripts', function () {
            ?>
            <script>
                const WP_LOAD_MORE_POST_TYPE = <?php echo json_encode($this->postType) ?>;
                const WP_LOAD_MORE_POSTS_PER_PAGE = <?php echo $this->postsPerPage; ?>;
                const WP_LOAD_MORE_SELECTOR = '<?php echo $this->loadMoreSelector; ?>';
                const WP_LOAD_MORE_OUTLET_SELECTOR = '<?php echo $this->outletSelector; ?>'
            </script>
            <?php

            wp_enqueue_script('wp-load-more', plugin_dir_url(__FILE__) . 'wp-load-more.js', [], '2.0');
        });
    }

    private function isRest()
    {
        function success($data)
        {
            wp_send_json($data, 200);
        }

        function error($data)
        {
            wp_send_json($data, 400);
        }

        $fields = [
            'postType',
            'postsPerPage',
            'pageNr'
        ];

        foreach ($fields as $field) {
            if (!isset($_GET[$field])) {
                error(['message' => 'Missing field: ' . $field]);
            }
        }

        $postType = $_GET['postType'];
        $postsPerPage = $_GET['postsPerPage'];
        $page = $_GET['pageNr'];

        if (!is_array($postType) && !is_string($postType)) {
            error(['message' => 'postType should be either an array or a string']);
            die();
        }
        if (!is_numeric($postsPerPage)) {
            error(['message' => 'postsPerPage should be an integer']);
            die();
        }
        if (!is_numeric($page)) {
            error(['message' => 'pageNr should be an integer']);
            die();
        }

        $this->postType = explode(',', $postType);
        $this->postsPerPage = $postsPerPage;
        $this->page = $page;

        $html = $this->loadPosts();
        $totalCount = 0;
        if (is_array($this->postType)) {
            foreach ($this->postType as $posttype) {
                $totalCount += wp_count_posts($posttype)->publish;
            }
        } else {
            $totalCount = wp_count_posts($this->postType)->publish;
        }
        $curCount = $this->page * $this->postsPerPage;

        $response = [
            'html' => $html,
            'totalCount' => intval($totalCount),
            'curCount' => $curCount,
        ];

        success($response);
    }

    private function isNormal()
    {
        $this->placeJs();
    }
}

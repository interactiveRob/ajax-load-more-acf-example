<?php
function rk_load_more_ajax_handler()
{
    $args = json_decode(stripslashes($_POST['query']), true);
    $new_page = $args['paged'] + 1;
    $render_quantity = 8;

    $range_bounds = [
        'quanity' => 8,
        'start' => $render_quantity * $args['paged'], 
        'end' => $render_quantity * $new_page
    ];

    if(!session_id()):
        session_start();
    endif;

    $article_components = $_SESSION['article_components'];

    $components_to_render = 
    array_filter($article_components, function($component, $index) use ($range_bounds) {
        return ($index > $range_bounds['start'] && $index <= $range_bounds['end']);
    }, ARRAY_FILTER_USE_BOTH);

    foreach ($components_to_render as $index => $article_component) :
        echo $article_component;
    endforeach;


    wp_die();
}

add_action('wp_ajax_load_more_posts', 'rk_load_more_ajax_handler');
add_action('wp_ajax_nopriv_load_more_posts', 'rk_load_more_ajax_handler');

function rk_start_session() {
    if(!session_id()):
        session_start();
    endif;
}
add_action('wp', 'rk_start_session');
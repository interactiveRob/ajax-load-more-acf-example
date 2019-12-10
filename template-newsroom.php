<?php
 /**
 * Template Name: Newsroom
 *
 * @package WordPress
 * @subpackage RK
 * @since RK 1.0
 */

    get_header();
?>

<div class="page">
    <div class="container newsroom-articles">
        <?php
            $fields =  get_fields();
            $page_headline = $fields['page_headline']; 
            $page_subtitle = $fields['page_subtitle']; 
            $news_articles = $fields['news_articles'];

            if($page_headline):
                $newsroom_intro = <<<NEWS
                <div class="newsroom-articles__intro text-center">
                    <h1 class="newsroom-articles__intro-headline">
                        $page_headline
                    </h1>
                    
                    <h4 class="newsroom-articles__intro-subtitle">
                        $page_subtitle
                    </h4>
                </div>
NEWS;
                echo $newsroom_intro;
            endif;
        ?>
        
        <div class="newsroom-articles__container">
            <?php
                $recent_article_fallback = $fields['recent_article_placeholder_image'];
                $featured_article_fallback = $fields['recent_article_placeholder_image'];

                $featured_article_html = '';
                $article_components = [];

                foreach($news_articles as $index => $news_article):
                
                    $article_title = $news_article['article_title']; 
                    $article_excerpt = $news_article['article_excerpt']; 
                    $article_link = $news_article['article_link']; 
                    $featured_article = $news_article['featured_article'];
                    $recent_article = !$featured_article;
                    $article_thumbnail = $news_article['article_thumbnail'];
                    
                    //set fallbacks for the article thumbnail
                    if(!$article_thumbnail):
                        $fallback_img = $featured_article ? $featured_article_fallback : $recent_article_fallback;

                        $article_thumbnail = $fallback_img;
                    endif;
                            
                    $article_thumbnail_url = $article_thumbnail['url'];      

                    //set trimmed excerpt
                    $more_tag = <<<EOT
                    ...&nbsp;<a class="more-link" target="_blank" href="$article_link">Read&nbsp;more</a>
EOT;
                    if($recent_article): 
                        $article_excerpt = wp_trim_words( $article_excerpt, 20, '');
                        $article_excerpt .= $more_tag;
                    endif;

                    if ($featured_article):
                        $article_thumbnail = $news_article['article_thumbnail'] ?: $featured_article_fallback;

                        $featured_article_html = <<<EOT
                            <div class="newsroom-articles__featured-article">
                                
                                <div class="newsroom-articles__section-tag">
                                    Featured
                                </div>
        
                                <div class="newsroom-articles__featured-article-img" style="background-image: url('$article_thumbnail_url');">
                                </div>

                                <div class="newsroom-articles__featured-article-content">

                                    <h2 class="newsroom-articles__featured-article-headline">
                                        $article_title 
                                    </h2>
                                    
                                    <div class="newsroom-articles__featured-article-excerpt">
                                        $article_excerpt
                                    </div> 

                                    <div class="newsroom-articles__featured-article-cta">
                                        <a class="callout" href="$article_link" target="_blank">
                                            <span>Read More</span>
                                        </a>
                                    </div>    

                                </div>
                            </div>
EOT;

                    else :

                        $recent_article_html = <<<EOT
                        <div class="newsroom-articles__recent-article">
                            <div class="newsroom-articles__recent-article-img">
                                <img src="$article_thumbnail_url">                                
                            </div>

                            <div class="newsroom-articles__recent-article-content">

                                <h2 class="newsroom-articles__recent-article-headline">
                                    $article_title
                                </h2>
                                
                                <div class="newsroom-articles__recent-article-excerpt">
                                    $article_excerpt
                                </div> 

                            </div>
                        </div>
EOT;
                        $article_components[$index] = $recent_article_html;

                    endif;
                
                endforeach;

                echo $featured_article_html;
            ?>  

            <div class="newsroom-articles__recent-articles">

                <div class="newsroom-articles__section-tag">
                    Recent
                </div>

                <?php
                    //save article components to the session for AJAX load more
                    //the session has already been started with a custom function in ajax.php
                    $_SESSION['article_components'] = $article_components;
                    
                    $components_to_render = 
                    array_filter($article_components, function($component, $index) {
                        return ($index <= 8);
                    }, ARRAY_FILTER_USE_BOTH);

                    foreach ($components_to_render as $index => $article_component) :
                        echo $article_component;
                    endforeach;
                ?>
                
            </div>
            
            <?php
                /* AJAX Load More Setup */
                $render_quantity = 8; 
                $current_page = 1;
                $count_articles = count($article_components);
                $has_full_groups = $count_articles % $render_quantity == 0;
                
                /*
                    Ternary explanation: if we have an even-split of articles per page 
                    (e.g. 24 articles makes 3 full pages), then just do simple division. 
                    Else divide, use intval to drop the decimal, and add a page for 
                    the aritcles that fall into the remainder 
                */
                $max_page = $has_full_groups ? 
                $count_articles/$render_quantity 
                : intval($count_articles/$render_quantity) + 1;

                $json_query_args = json_encode(
                    [
                        'article_type' => 'newsroom',
                        'paged' => $current_page,
                        'page' => $current_page,
                        'append_target' => '.newsroom-articles__recent-articles'
                    ]
                );

                $ajax_btn = <<<AJAX
                    <div class="newsroom-articles__load-more" data-js="load-more" data-query=$json_query_args data-page="$current_page" data-maxpage="$max_page">
                        <a class="white callout theme-dark" href="javascript:;" target="">Load More</a>
                    </div>
AJAX;
                echo $ajax_btn;            
            ?>

        </div> <!-- end .newsroom-articles__content -->
    </div> <!-- end .newsroom-articles__container -->
</div> <!-- end .page -->

<?php
    get_footer();
?>

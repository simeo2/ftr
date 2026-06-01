<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="ftr-grid">
    <?php if ( empty($reviews) ) : ?>
        <p><?php esc_html_e( 'No reviews found.', 'free-tp-reviews' ); ?></p>
    <?php else : ?>
        <?php foreach ( $reviews as $review ) : 
            $words = explode(' ', $review['author']);
            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        ?>
            <div class="ftr-grid-item">
                <div class="ftr-header">
                    <?php if ( !empty($review['avatar']) ) : ?>
                        <img src="<?php echo esc_url($review['avatar']); ?>" alt="<?php echo esc_attr($review['author']); ?>" class="ftr-avatar">
                    <?php else : ?>
                        <div class="ftr-avatar-fallback"><?php echo esc_html($initials); ?></div>
                    <?php endif; ?>
                    
                    <div class="ftr-meta">
                        <h4 class="ftr-author"><?php echo esc_html( $review['author'] ); ?></h4>
                        <span class="ftr-date"><?php echo esc_html( wp_date( 'Y-m-d', strtotime($review['date'])) ); ?></span>
                    </div>
                </div>
                
                <div class="ftr-body">
                    <img src="<?php echo esc_url( FTR_URL . 'assets/images/stars-' . $review['rating'] . '.svg' ); ?>" alt="<?php echo esc_attr($review['rating']); ?> Stars" class="ftr-stars-img">
                    
                    <?php if ( !empty($review['title_tr']) ) : ?>
                        <h5 class="ftr-review-title"><?php echo esc_html( $review['title_tr'] ); ?></h5>
                    <?php endif; ?>
                    
                    <p class="ftr-text"><?php echo esc_html( $review['text_tr'] ); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
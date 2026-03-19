<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="ftr-grid">
    <?php if ( empty($reviews) ) : ?>
        <p><?php esc_html_e( 'No reviews found.', 'free-tp-reviews' ); ?></p>
    <?php else : ?>
        <?php foreach ( $reviews as $review ) : ?>
            <div class="ftr-grid-item">
                <div class="ftr-header">
                    <?php if ( !empty($review['avatar']) ) : ?>
                        <img src="<?php echo esc_url($review['avatar']); ?>" alt="<?php echo esc_attr($review['author']); ?>" class="ftr-avatar">
                    <?php else : ?>
                        <div class="ftr-avatar-fallback"><?php echo esc_html(substr($review['author'], 0, 1)); ?></div>
                    <?php endif; ?>
                    
                    <div class="ftr-meta">
                        <h4 class="ftr-author"><?php echo esc_html( $review['author'] ); ?></h4>
                        <div class="ftr-rating" data-rating="<?php echo esc_attr( $review['rating'] ); ?>">
                            <?php echo str_repeat('⭐', $review['rating']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="ftr-body">
                    <p class="ftr-text"><?php echo esc_html( $review['text_tr'] ); ?></p>
                    <span class="ftr-date"><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime($review['date'])) ); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php

/**
 * Created by PhpStorm.
 * User: barreto
 * Date: 16/12/16
 * Time: 14:40
 */
class R3wInstaFeedWidget extends WP_Widget{


    public function __construct() {
        $widget_ops = array(
            'classname' => 'r3wif_widget',
            'description' => 'R3W Instafeed',
        );

        $control_ops = array( 'title' => 'default title' );

        parent::__construct( 'r3wif_widget', 'R3W Instafeed', $widget_ops, $control_ops );
    }

    public function widget( $args, $instance ) {

        $userid = apply_filters( 'widget_title', get_option('r3wif-userId') );
        $accessToken = apply_filters( 'widget_title', get_option('r3wif-accessToken') );

//        $title = apply_filters( 'widget_title', $instance['title'] );
//        $hashtag = apply_filters( 'widget_title', $instance['hashtag'] );

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = __( 'Album Title', 'r3wif_widget_domain' );
        }

        if ( isset( $instance[ 'hashtag' ] ) ) {
            $hashtag = $instance[ 'hashtag' ];
        } else {
            $hashtag = __( 'Hashtag', 'r3wif_widget_domain' );
        }

        echo $args['before_widget'];

        if (!empty($title))
            echo "<h2>".$title."</h2>";

        echo "<div id=\"instafeed\"></div>";
        ?>
        <script>
            jQuery(document).ready(function() {
                var feed = new Instafeed({
                    get: 'user',
                    userId: '<?= $userid; ?>',
                    resolution: 'low_resolution',
                    <?php if(!empty($hashtag)) { ?>
                    filter: function(image) {
                        return image.tags.indexOf('<?= $hashtag; ?>') >= 0;
                    },
                    <?php } ?>
                    accessToken: '<?= $accessToken; ?>',
                    template: '<div class="instafeedImage"><img src="{{image}}" /></div>'
                });
                feed.run();
            });
        </script>
        <?php
        echo $args['after_widget'];
    }


    public function form( $instance ) {

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = __( 'Album Title', 'r3wif_widget_domain' );
        }

        if ( isset( $instance[ 'hashtag' ] ) ) {
            $hashtag = $instance[ 'hashtag' ];
        } else {
            $hashtag = __( 'Hashtag', 'r3wif_widget_domain' );
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'hashtag' ); ?>"><?php _e( 'Hashtag:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'hashtag' ); ?>" name="<?php echo $this->get_field_name( 'hashtag' ); ?>" type="text" value="<?php echo esc_attr( $hashtag ); ?>" />
            <span>Use hashtags without the '#'</span>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['hashtag'] = ( ! empty( $new_instance['hashtag'] ) ) ? strip_tags( $new_instance['hashtag'] ) : '';
        return $instance;
    }

}

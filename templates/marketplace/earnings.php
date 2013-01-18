<?php
/**
 * Holds the earnings template
 * 
 * @package		Marketplace
 * @subpackage	Main
 * @author		Boris Glumpler
 * @copyright 	Copyright (c) 2010 - 2012, Themekraft
 * @link		https://shabushabu.eu
 * @license		http://www.opensource.org/licenses/gpl-3.0.php GPL License
 * @since 		Marketplace 0.9
 * @filesource
 */
 
global $wp_locale, $wpdb;

get_header( 'buddypress' ); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'membership_before_member_home_content' ); ?>

			<div id="item-header" role="complementary">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php bp_get_displayed_user_nav(); ?>

						<?php do_action( 'bp_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php do_action( 'membership_before_member_body' ); ?>
	
		        <table class="marketplace-income" id="mp-marketplace-income">
		            <thead>
		                <tr>
		                    <th class="rate"><?php _e( 'Rate', 'marketplace' ) ?></th>
		                    <th class="income-month"><?php _e( 'Income current month', 'marketplace' ) ?></th>
		                    <th class="income-total"><?php _e( 'Total income', 'marketplace' )?></th>
		                </tr>
		            </thead>
		
		            <tbody>
		                <tr>
		                    <td class="rate"><?php echo mp_get_user_rate( bp_displayed_user_id() ) ?>%</td>
		                    <td class="income-month">
		                    	<?php echo mp_get_user_comission( bp_displayed_user_id(), date( 'Y-n', strtotime( 'now' ) ) ) ?>
		                    	(<?php echo mp_get_user_income( bp_displayed_user_id(), date( 'Y-n', strtotime( 'now' ) ) ) ?>)
		                    </td>
		                    <td class="income-total">
		                    	<?php echo mp_get_user_comission( bp_displayed_user_id() ) ?>
		                    	(<?php echo mp_get_user_income( bp_displayed_user_id() ) ?>)
		                    </td>
		                </tr>
		            </tbody>
		        </table>
		        
				<?php
				$month = isset( $_GET['month'] ) ? $_GET['month'] : date( 'Y-m', strtotime( 'now' ) );
				$item_count = 0;
				
				$y = substr( $month, 0, 4 );
				$m = substr( $month, -2 );
				
				$args  = array(
					'post_type'   => 'shop_order',
					'numberposts' => -1,
					'year'		  => $y,
					'monthnum'	  => $m
				);
				
				$orders = get_posts( $args );
				
				$user_rate = mp_get_user_rate( bp_displayed_user_id() );				

				$months = $wpdb->get_results( $wpdb->prepare( "
					SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
					FROM $wpdb->posts
					WHERE post_type = %s
					ORDER BY post_date DESC
				", 'shop_order' ) );
				?>
				
				<form method="get">
					<select name="month">
					<?php
					foreach ( $months as $arc_row ) :
						if( 0 == $arc_row->year )
							continue;
			
						$mo = zeroise( $arc_row->month, 2 );
						$year = $arc_row->year;
			
						printf( "<option %s value='%s'>%s</option>\n",
							selected( $month, $year .'-'. $mo, false ),
							esc_attr( $year .'-'. $mo ),
							/* translators: 1: month name, 2: 4-digit year */
							sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $mo ), $year )
						);
					endforeach;
					?>
					</select>
					
					<input type="submit" id="filter-month" value="<?php _e( 'Filter', 'marketplace' ) ?>" />
				</form>
		        
		        <table class="marketplace-sales" id="mp-marketplace-sales">
		            <thead>
		                <tr>
		                    <th class="product"><?php _e( 'Product', 'marketplace' ) ?></th>
		                    <th class="date"><?php _e( 'Date', 'marketplace' ) ?></th>
		                    <th class="total"><?php _e( 'Total amount', 'marketplace' ) ?></th>
		                    <th class="tax"><?php _e( 'Tax', 'marketplace' ) ?></th>
		                    <th class="earnings"><?php _e( 'Earnings', 'marketplace' ) ?></th>
		                </tr>
		            </thead>
		            <tbody>
		            	<?php
		            	if( count( $orders ) > 0 ) :
							foreach( $orders as $result ) :
								$order = new WC_Order();
								$order->populate( $result );
																
								foreach( $order->get_items() as $item ) :
									$product = get_post( $item['id'] );
									
									if( $product->post_author != bp_displayed_user_id() )
										continue;
										
									$item_count++;

									$item_total	 	 = $order->get_line_subtotal( $item ) - $order->get_line_tax( $item );
									$author_earnings = ( $item_total / 100 ) * $user_rate;
									?>
					                <tr>
					                    <td class="product"><a href="<?php echo get_permalink( $product->ID ) ?>"><?php echo esc_html( $product->post_title ) ?></a></td>
					                    <td class="date"><?php echo mysql2date( get_option( 'date_format' ), $result->post_date ) ?></td>
					                    <td class="total"><?php echo woocommerce_price( $order->get_line_subtotal( $item ) ) ?></td>
					                    <td class="taxes"><?php echo woocommerce_price( $order->get_line_tax( $item ) ) ?></td>
					                    <td class="earnings"><?php echo woocommerce_price( $author_earnings ) ?></td>
					                </tr>
					                <?php
								endforeach;
							endforeach;
						endif;

						if( $item_count <= 0 ) :
							?>
			                <tr>
			                    <td colspan="5">
			                    	<?php printf( __( 'No sales for %s %s', 'marketplace' ), $wp_locale->get_month( $m ), $y ) ?>
			                    </td>
			                </tr>
			                <?php
						endif;
						?>
		            </tbody>
		        </table>

				<?php do_action( 'membership_after_member_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'membership_after_member_home_content' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>
<?php
/*  
Plugin Name: Jigoshop Basic Bundle Shipping
Plugin URI: http://www.polevaultweb.com/plugins/jigoshop-basic-bundle-shipping/ 
Description: Plugin to extend the Jigoshop shipping rates with a basic bundle rate.
Author: polevaultweb 
Version: 1.0.2
Author URI: http://www.polevaultweb.com/

Copyright 2012  polevaultweb  (email : info@polevaultweb.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


add_action( 'plugins_loaded', 'jbbs_jigoshop_basic_bundle_shipping_load', 0 );

function jbbs_jigoshop_basic_bundle_shipping_load() {
	
	if ( !class_exists( 'jigoshop_shipping_method' ) ) return;
	
	function add_basic_bundle_method( $methods ) {
		$methods[] = 'jbbs_jigoshop_basic_bundle_shipping'; 
		return $methods;
	}
	add_filter('jigoshop_shipping_methods', 'add_basic_bundle_method' );	
	
	class jbbs_jigoshop_basic_bundle_shipping extends jigoshop_shipping_method {

		private $jigoshop_verson;
		public function __construct() {
			
			// Get Jigoshop version
			$path = dirname( dirname(__FILE__) ) .'/jigoshop/jigoshop.php';
			$default_headers = array( 'Version' => 'Version',  'Name' => 'Plugin Name');
			$plugin_data = get_file_data( $path, $default_headers, 'plugin' );
			$this->jigoshop_verson = $plugin_data['Version'];
			
			if ( version_compare( $this->jigoshop_verson, '1.4', '<' ) ) {
				$this->id 			= 'basic_bundle';
				$this->enabled		= get_option('jigoshop_basic_bundle_enabled');
				$this->title 		= get_option('jigoshop_basic_bundle_title');
				$this->first 		= get_option('jigoshop_basic_bundle_first');
				$this->further 		= get_option('jigoshop_basic_bundle_further');
				$this->cap 			= get_option('jigoshop_basic_bundle_cap');
				if (isset( jigoshop_session::instance()->chosen_shipping_method_id ) && jigoshop_session::instance()->chosen_shipping_method_id==$this->id) $this->chosen = true;
	
				add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
	
				add_option('jigoshop_basic_bundle_first', '3.50');
				add_option('jigoshop_basic_bundle_further', '1.00');
				add_option('jigoshop_basic_bundle_cap', '5');
				add_option('jigoshop_basic_bundle_title', 'Basic Bundle Rate');
			
			} else {
				// Post 1.4
				parent::__construct();
				$this->id 			= 'basic_bundle';
				$this->enabled		= Jigoshop_Base::get_options()->get_option('jigoshop_basic_bundle_enabled');
				$this->title 		= Jigoshop_Base::get_options()->get_option('jigoshop_basic_bundle_title');
				$this->first 		= Jigoshop_Base::get_options()->get_option('jigoshop_basic_bundle_first');
				$this->further 		= Jigoshop_Base::get_options()->get_option('jigoshop_basic_bundle_further');
				$this->cap 			= Jigoshop_Base::get_options()->get_option('jigoshop_basic_bundle_cap');
				
			}
			
		}
	
		/**
		 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
		 *
		 * These should be installed on the Jigoshop_Options 'Shipping' tab
		 *
		 */	
		 
		// Post 1.4 versions
		protected function get_default_options() {
		
			$defaults = array();
			
			// Define the Section name for the Jigoshop_Options
			$defaults[] = array( 'name' => __('Basic Bundle Rate', 'jigoshop'), 'type' => 'title', 'desc' => __('Set a price for the first product and another price for future products.', 'jigoshop') );
			
			// List each option in order of appearance with details
			$defaults[] = array(
				'name'		=> __('Enable Basic Bundle Rate','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_basic_bundle_enabled',
				'std' 		=> 'yes',
				'type' 		=> 'checkbox',
				'choices'	=> array(
					'no'			=> __('No', 'jigoshop'),
					'yes'			=> __('Yes', 'jigoshop')
				)
			);
			
			$defaults[] = array(
				'name'		=> __('Method Title','jigoshop'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
				'id' 		=> 'jigoshop_basic_bundle_title',
				'std' 		=> __('Basic Bundle Rate','jigoshop'),
				'type' 		=> 'text'
			);
			
			$defaults[] = array(
				'name'		=> __('Price for First Product','jigoshop'),
				'desc' 		=> '',
				'type' 		=> 'decimal',
				'tip' 		=> __('Cost excluding tax. Enter an amount, e.g. 2.50.','jigoshop'),
				'id' 		=> 'jigoshop_basic_bundle_first',
				'std' 		=> '3.50',
			);
			
			$defaults[] = array(
				'name'		=> __('Price for Further Products','jigoshop'),
				'desc' 		=> '',
				'type' 		=> 'decimal',
				'tip' 		=> __('Cost excluding tax. Enter an amount, e.g. 2.50.','jigoshop'),
				'id' 		=> 'jigoshop_basic_bundle_further',
				'std' 		=> '1.OO',
			);
			$defaults[] = array(
				'name'		=> __('Product Number Cap','jigoshop'),
				'desc' 		=> '',
				'type' 		=> 'decimal',
				'tip' 		=> __('This controls the number of products in the cart before the shipping total is capped.','jigoshop'),
				'id' 		=> 'jigoshop_basic_bundle_cap',
				'std' 		=> '5',
			);
			return $defaults;
		}
		
		// Pre 1.4 versions
		public function admin_options() {
			if ( version_compare( $this->jigoshop_verson, '1.4', '>=' ) ) return;
			?>
			<thead><tr><th scope="col" colspan="2"><h3 class="title"><?php _e('Basic Bundle Rate', 'jigoshop'); ?></h3></th></tr></thead>
			<tr>
				<th scope="row"><?php _e('Enable basic bundle rate', 'jigoshop') ?></th>
				<td class="forminp">
					<select name="jigoshop_basic_bundle_enabled" id="jigoshop_basic_bundle_enabled" style="min-width:100px;">
						<option value="yes" <?php if (get_option('jigoshop_basic_bundle_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
						<option value="no" <?php if (get_option('jigoshop_basic_bundle_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?></th>
				<td class="forminp">
					<input type="text" name="jigoshop_basic_bundle_title" id="jigoshop_basic_bundle_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_basic_bundle_title')) echo $value; else echo 'Basic Bundle Rate'; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Price for first product', 'jigoshop') ?></th>
				<td class="forminp">
				 <input type="text" name="jigoshop_basic_bundle_first" id="jigoshop_basic_bundle_first" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_basic_bundle_first')) echo $value; else echo '3.50'; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Price for further products', 'jigoshop') ?></th>
				<td class="forminp">
				 <input type="text" name="jigoshop_basic_bundle_further" id="jigoshop_basic_bundle_further" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_basic_bundle_further')) echo $value; else echo '1.00'; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><a href="#" tip="<?php _e('This controls the number of products in the cart before the shipping total is capped.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Product number cap', 'jigoshop') ?></th>
				<td class="forminp">
				 <input type="text" name="jigoshop_basic_bundle_cap" id="jigoshop_basic_bundle_cap" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_basic_bundle_cap')) echo $value; else echo '5'; ?>" />
				<br/><small>The current capped price is 1 x <?php echo get_option('jigoshop_basic_bundle_first'); ?> + ( <?php echo get_option('jigoshop_basic_bundle_cap') - 1; ?> x <?php echo get_option('jigoshop_basic_bundle_further'); ?> ) = <?php echo round(get_option('jigoshop_basic_bundle_first') + ((get_option('jigoshop_basic_bundle_cap') - 1) * get_option('jigoshop_basic_bundle_further')),2);  ?></small>
				</td>
			</tr>
			
			<?php
		}

		// Pre 1.4 versions
		public function process_admin_options() {

			if(isset($_POST['jigoshop_basic_bundle_enabled'])) update_option('jigoshop_basic_bundle_enabled', jigowatt_clean($_POST['jigoshop_basic_bundle_enabled'])); else @delete_option('jigoshop_basic_bundle_enabled');
			if(isset($_POST['jigoshop_basic_bundle_title'])) update_option('jigoshop_basic_bundle_title', jigowatt_clean($_POST['jigoshop_basic_bundle_title'])); else @delete_option('jigoshop_basic_bundle_title');
			if(isset($_POST['jigoshop_basic_bundle_first'])) update_option('jigoshop_basic_bundle_first', jigowatt_clean($_POST['jigoshop_basic_bundle_first'])); else @delete_option('jigoshop_basic_bundle_first');
			if(isset($_POST['jigoshop_basic_bundle_further'])) update_option('jigoshop_basic_bundle_further', jigowatt_clean($_POST['jigoshop_basic_bundle_further'])); else @delete_option('jigoshop_basic_bundle_further');
			if(isset($_POST['jigoshop_basic_bundle_cap'])) update_option('jigoshop_basic_bundle_cap', jigowatt_clean($_POST['jigoshop_basic_bundle_cap'])); else @delete_option('jigoshop_basic_bundle_cap');

		}

		
		// All versions
	    public function calculate_shipping() {
		    $this->shipping_total 	= 0;
			$this->shipping_tax 	= 0;
			$this->shipping_label 	= $this->title;
			
			if (sizeof(jigoshop_cart::$cart_contents)>0) {
				
				$num_items = 0;
				foreach (jigoshop_cart::$cart_contents as $item_id => $values) {
					if (!in_array($values['data']->product_type, array( 'virtual', 'downloadable' ))) {
						$num_items = $num_items + $values['quantity'];
					}
				}
					  
				if ($num_items == 1) {

					$this->shipping_total = $this->first;
				
				} else {
				
					$num_products = $this->cap;
					if ($num_items < $num_products) {
						$num_products = $num_items;
					}
					
					$cost = $this->first + (($num_products - 1) * $this->further);
					$this->shipping_total = $cost;
				
				}
			}
	    }
	
	}
}

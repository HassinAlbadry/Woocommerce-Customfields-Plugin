<?php
/*
 * Plugin Name:      HA Woocommerce Custom Fields
 * Description:       Adding input custom fields to woocommerce product page
 * Version:           1.0
 * Author:            Hassin Albadry
 
 */



// admin side


//adding extra option to panel in product page 
 
 add_filter('woocommerce_product_data_tabs','ha_add_to_panel');


    function ha_add_to_panel($tabs){

        $ha_tabs=array(
                    'ha_extra'        => array(
                    'label'    => __( 'Custom Fields', 'woocommerce' ),
                    'target'   => 'ha_new_field',
                    'class'    => array( 'hide_if_grouped' ),
                    'priority' => 10
                )

            );

        $final=array_merge($tabs,$ha_tabs);
        return $final;
    }

   

//adding content to the newly created options on admin panel

add_action('woocommerce_product_data_panels','ha_add_content');

function ha_add_content(){

    ?>

 <div id="ha_new_field" class="panel woocommerce_options_panel">

        <?php 

        //input field to ask admin how many input fields to add to product page
        woocommerce_wp_text_input(
            array(
                'id'          => 'ha_input',
                'name'        => 'ha_input', 
                'value'       => '',
                'label'       => 'How many custom fields to add?',
                'placeholder' => 'enter number of fields',
                'description' => 'Determing number of fields to add' ,
            )
        );

          //input field to ask admin how many input fields to add to product page
        woocommerce_wp_text_input(
            array(
                'id'          => 'ha_name_input',
                'name'        => 'ha_name_input', 
                'value'       => '',
                'label'       => 'name of custom fields?',
                'placeholder' => 'enter name of each field followed by a coma',
                'description' => 'Determing the name of fields to add' ,
            )
        );

        


// checkbox in admin page to ask admin if wanting to enable custom fields in front end
        woocommerce_wp_checkbox(
                array(
                    'id'            => 'ha_enable',
                    'name'          =>'ha_enable',
                    'value'         => '',
                    'wrapper_class' => 'show_if_simple show_if_variable',
                    'label'         =>( 'enable custom fields' ),
                    'description'   =>( 'This is to enable custom field for listing' ),
                )
            );

        ?>

</div>



<?php
}


// update admin  meta data in database attached to saving post so it can be used in product page for that item

add_action( 'save_post', 'save_postdata_custom' );

function save_postdata_custom( $post_id){

if(isset($_POST['ha_input'])){
    update_post_meta( $post_id, 'number of custom fields', sanitize_text_field($_POST['ha_input'] ));
}

if(isset($_POST['ha_name_input'])){

update_post_meta( $post_id, 'name of custom fields', sanitize_text_field($_POST['ha_name_input']));

}

if(isset($_POST['ha_enable'])){
update_post_meta( $post_id, 'enable_custom_fields','yes' );

}else{
    update_post_meta( $post_id, 'enable_custom_fields','No' );
}


}



 

 

//add front end custom fields

add_action('woocommerce_before_add_to_cart_button','add_custom_fields_front_end');


function add_custom_fields_front_end(){
 //check if its enabled

 // getting product ids to get post meta drom database 
global $product;
$id = $product->get_id();

$enabled= get_post_meta($id,'enable_custom_fields');
$num_of_fields= get_post_meta($id,'number of custom fields');
$name_of_fields= get_post_meta($id,'name of custom fields');

$num_of_fields_to_str=0;
$name_of_fields_to_arr=array();
 
$i=0;

// loop thru name meta to get name of fields that admin set and seperate by coma to add to $name_of_fields_array
foreach ($name_of_fields as $value) {
    
    $name_of_fields_to_arr=explode(',', $value);


}


foreach ($num_of_fields as $value) {
    $num_of_fields_to_str=$value;
}
$num_of_fields_to_int = (int)$num_of_fields_to_str;

// check to see if meta data checkbox is enabled to determine if product page should display custom fields 
if (in_array("yes", $enabled)){

     // start displaying custom fields names and input fields to get value from user
    ?>


    <?php  while ($i <$num_of_fields_to_str):  ?>
     <?php foreach ($name_of_fields_to_arr as $value) : ?>

        <!-- hidden field to be used later to display field names in cart page --> 
  <input type="hidden" id="field_name" name="field_name[]" value="<?php echo esc_html($value); ?>">
  <label for="fname"><?php echo esc_html($value); ?></label>

  <input type="text" id="hacustomfield" name="hacustomfield[]"><br><br>

  <?php 

$i++;
     endforeach;
endwhile; ?>


  <?php
    
     
}

}


 //add item data from product page to cart

add_filter('woocommerce_add_cart_item_data','ha_add_fields_to_cart',10,2);

    function ha_add_fields_to_cart($cart_item_data,$product_id){
   
        
    // post both custom fields names and values from product page and add to cart item data to use later for display in cart
        
    $sanitized_customfield_arr_val=array();
    $sanitized_customfield_arr_name=array();


        if(isset($_POST['hacustomfield'])){

            // loop thru array input to sanitize each value in array before adding to $cart item data
            foreach ($_POST['hacustomfield'] as $value) {
                sanitize_text_field($value);
                array_push($sanitized_customfield_arr_val,$value);
            }

        $cart_item_data['custom']=$sanitized_customfield_arr_val ;

     }


     if(isset($_POST['field_name'])){

// loop thru array input to sanitize each value in array before adding to $cart item data
        foreach ($_POST['field_name'] as $value) {
            sanitize_text_field($value);
            array_push($sanitized_customfield_arr_name,$value);
        }
         $cart_item_data['nameoffields']=$sanitized_customfield_arr_name;

    }
return $cart_item_data;
    }
     





// display data in cart: custom fields name and values by using cart item data into item data

add_filter('woocommerce_get_item_data','ha_display_fields_to_cart',10,2);

function ha_display_fields_to_cart($item_data, $cart_item_data){

   // var to loop thru cart item data labels along side with $values
    $i=0;
       foreach ($cart_item_data['custom'] as   $val) {
          
        //adding array of custom fields names and values to $item_data to be displayed in cart page.
        $item_data[]=array(

            'name'=>$cart_item_data['nameoffields'][$i],
            'value'=>$val
            
           
              );
           $i++;
            }
            

return $item_data;

}





 




 ?>
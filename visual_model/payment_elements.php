<?php

/**
 * Auxiliary function: auxiliary_fn_paypal_payment_select_out *
 * Elimina campo select por defecto del componente Paypal_payment_accept (adaptación plugin Paypal_payment ) .
 * Incluye input hidden para el tratamiento del monto a pagar. Cambia URL de cancelación del componente Paypal_payment_accept.
 */
function auxiliary_fn_paypal_payment_select_out($value = '') {	
	update_option('wp_pp_cancel_url' ,esc_url(get_home_url()));	
	$shortcode = Paypal_payment_accept();
	$shortcode_select_out = strip_tags($shortcode, '<div><form><input><script>');
	
	$itemName1 = get_option('wp_pp_payment_item1');   
	$itemName2 = get_option('wp_pp_payment_item2');   
	$itemName3 = get_option('wp_pp_payment_item3');   
	$itemName4 = get_option('wp_pp_payment_item4');   
	$itemName5 = get_option('wp_pp_payment_item5');   
	$itemName6 = get_option('wp_pp_payment_item6');
		
	if (!empty($itemName1)) $shortcode_select_out = str_replace($itemName1, '', $shortcode_select_out);
	if (!empty($itemName2)) $shortcode_select_out = str_replace($itemName2, '', $shortcode_select_out);
	if (!empty($itemName3)) $shortcode_select_out = str_replace($itemName3, '', $shortcode_select_out);
	if (!empty($itemName4)) $shortcode_select_out = str_replace($itemName4, '', $shortcode_select_out);
	if (!empty($itemName5)) $shortcode_select_out = str_replace($itemName5, '', $shortcode_select_out);
	if (!empty($itemName6)) $shortcode_select_out = str_replace($itemName6, '', $shortcode_select_out);
 	
	if(!empty($value) && isset($value)){       
	    $arr_shortcode_select_out = explode('</form>', $shortcode_select_out );	   
        $insert_input_hidden = '<input type="hidden" id="amount" name="amount" value="'.esc_attr($value).'" />';
        $shortcode_select_out = $arr_shortcode_select_out[0].$insert_input_hidden.'</form>'.$arr_shortcode_select_out[1];	
	}
	return $shortcode_select_out;	
}
/**
 * Auxiliary function: auxiliary_fn_add_return_payment_selected *
 * Identificando el tipo de crdito o comodin seleccionado antes de su pago por PAYPAL
 */
function auxiliary_fn_add_return_payment_selected($param_encryp){
    $param_encryp = md5($param_encryp);
    ?><script>
        document.addEventListener('readystatechange', event => { 
            if (event.target.readyState === "complete") {	
                form_pay = document.getElementsByClassName('wp_accept_pp_button_form_classic'),
                form_redsys = document.getElementById('form_tpv_submit');
                param_encryp = <?php echo json_encode($param_encryp)?>;
                form_pay[0].onsubmit = function(){
                    before_submit(param_encryp);
                }
                form_redsys.onmousedown = function(){ //onmousedown
                    before_submit(param_encryp);
                    var importe = document.getElementById("amount").value,
                        producto = document.getElementsByName("item_name"),
                        input_return = document.getElementsByName("return"),
                        input_return_cancel = document.getElementsByName("cancel_return");
                    document.getElementById("orderNumber").value = param_encryp.substring(0, 8);
                    document.getElementById("orderDesc").value = producto[0].value;
                    document.getElementById("amountTPV").value = importe;
                    document.getElementById("url_ok").value = input_return[0].value;
                    document.getElementById("url_ko").value = input_return_cancel[0].value;
                }               
               
                 function before_submit (param_encryp){
                    var amount_select = document.getElementById("amount"),
                        return_input = document.getElementsByName('return'),
                        select_id =  amount_select.options[amount_select.selectedIndex].id;
                        
                        if(return_input.length > 0){
                            var value_ini = return_input[0].value,
                                index_end = value_ini.indexOf('&'),                               
                                only_main_url = (index_end == -1) ? value_ini : value_ini.substring(0, index_end); 
                            return_input[0].value =  only_main_url + '&i='+select_id+'&e='+ param_encryp;
                        }
                        
                }
            }
        });
   </script><?php    
}
/**
 * Shortcode: wp_ext_paypal_payment_acquisition *
 * Permite efectuar el pago directo de la tarifa por adquisición.
 */
function wp_ext_paypal_payment_acquisition () {
	
	if(get_current_user_id() == 0)
		return "<script> window.location.replace(".json_encode(get_home_url())."+'/account/login/');</script>";
	
	$value = get_option('wp_pp_payment_value1');
    $impuesto_iva = get_option('wp_pp_payment_value2');
    $impuesto_iva = (empty($impuesto_iva)) ? 0 : $impuesto_iva;
    $import_pay = $value + (($impuesto_iva/100) * $value);
	$id_post_adq = $_GET['id_padq'];
	$post_adq = get_post($id_post_adq);
	update_option('wp_pp_payment_subject','Producto: '.$post_adq->post_title);	
	update_option('wp_pp_return_url' ,esc_url($post_adq->guid));	
	return auxiliary_fn_paypal_payment_select_out($import_pay); 
} 
/**
 * Shortcode: wp_ext_paypal_payment_acq_reserved
 * Permite efectuar el pago de la tarifa de comodín
 */
function wp_ext_paypal_payment_acq_reserved () {
	global $wpdb;
	if(get_current_user_id() == 0)
		return "<script> window.location.replace(".json_encode(get_home_url())."+'/account/login/');</script>";
	
    //Restringiendo numeros de comodines permitidos
	$post_adq = get_post();
    $cant_reserved = Acquisition_Reserved::wp_ext_total_acquisition_reserved();
	$param_encript = md5(get_current_user_id().$cant_reserved);    	
	update_option('wp_pp_return_url', esc_url(get_home_url().'?orgn='.$param_encript));
    update_option('wp_pp_payment_subject','Producto: '.$post_adq->post_title);	
    
    //determinando valor del impuesto IVA
    $impuesto_iva = get_option('wp_pp_payment_value2');
    $impuesto_iva = (empty($impuesto_iva)) ? 0 : $impuesto_iva;
    
    //determinando opciones de pago por adquisiciones pendientes(comodines)
    $query = "SELECT id, name_type, value FROM wpda_ext_type_pkg_credit WHERE type_credit = 1";
    $type_credit = $wpdb->get_results($query, ARRAY_A); 
    $output = '';
     if((!empty($type_credit) && isset($type_credit))){
          $output .= '<select id="amount" name="amount" class="">';
         foreach($type_credit as $index => $val){
            $id = $val['id'];
            $import_pay_ini = $val['value'];
            $import_iva = round((($impuesto_iva/100) * $import_pay_ini),2);
            $import_pay = $import_pay_ini + $import_iva;				 
            $output .= '<option id="'.$id.'" value="'.esc_attr($import_pay).'">'.esc_attr($val['name_type']).':  '.esc_attr($import_pay_ini).'€ + '.$import_iva.'€ de IVA('.$impuesto_iva.'%) </option>';
         }
        $output .= '</select>';		
        $opcion_pay = auxiliary_fn_paypal_payment_select_out(); 
        $opcion_pay = str_replace('<div class="wpapp_payment_button">', $output.'<div class="wpapp_payment_button">' ,$opcion_pay);
        auxiliary_fn_add_return_payment_selected($cant_reserved);
        return $opcion_pay;
    }
     return '<p> Error de conexi&oacute;n al mostrar paquetes de cr&eacute;ditos.</p>';
}
/**
 * Shortcode: wp_ext_paypal_payment_credits
 * Permite efectuar el pago de los créditos
 */
function wp_ext_paypal_payment_credits () {
    global $wpdb;
     
    if(get_current_user_id() == 0)
         return "<script> window.location.replace(".json_encode(get_home_url())."+'/account/login/');</script>";
     
    $post_adq = get_post();
    $impuesto_iva = get_option('wp_pp_payment_value2');
    $impuesto_iva = (empty($impuesto_iva)) ? 0 : $impuesto_iva;
    
    $query = "SELECT id, name_type, value FROM wpda_ext_type_pkg_credit WHERE type_credit = 0";
    $type_credit = $wpdb->get_results($query, ARRAY_A); 
    $output = '';
     if((!empty($type_credit) && isset($type_credit))){
          $output .= '<select id="amount" name="amount" class="">';
         foreach($type_credit as $index => $val){
            $id = $val['id'];
            $import_pay_ini = $val['value'];
            $import_iva = round((($impuesto_iva/100) * $import_pay_ini),2);
            $import_pay = $import_pay_ini + $import_iva;				 
            $output .= '<option id="'.$id.'" value="'.esc_attr($import_pay).'">'.esc_attr($val['name_type']).':  '.esc_attr($import_pay_ini).'€ + '.$import_iva.'€ de IVA('.$impuesto_iva.'%) </option>';
         }
         $output .= '</select>';
        $credit_active = Credits::wp_ext_array_credits_actives('acq_credit_pending');
        $param_encript = md5(get_current_user_id().$credit_active);  		
        update_option('wp_pp_return_url', esc_url(get_home_url().'?orgn='.$param_encript));			
        update_option('wp_pp_payment_subject','Producto: '.$post_adq->post_title);		
        $opcion_pay = auxiliary_fn_paypal_payment_select_out(); 
        $opcion_pay = str_replace('<div class="wpapp_payment_button">', $output.'<div class="wpapp_payment_button">' ,$opcion_pay);
        auxiliary_fn_add_return_payment_selected($credit_active);
        return $opcion_pay;
     }
     return '<p> Error de conexi&oacute;n al mostrar paquetes de cr&eacute;ditos.</p>';
 }
/**
 * Shortcode: wp_ext_paypal_value_by_acquisition *
 * Muestra el valor de la tarifa por obtención de los datos del donante.
 */
function wp_ext_paypal_value_by_acquisition () { 	
	return get_option('wp_pp_payment_value1');	
} 
/**
 * Shortcode: wp_ext_paypal_value_tax_iva *
 * Muestra el valor de la taxa de IVA en decimales.
 */
function wp_ext_paypal_value_tax_iva () { 	
	return get_option('wp_pp_payment_value2');	
}
/**
 * Shortcode: wp_ext_paypal_title_val_acq *
 * Muestra el text descriptivo de la tarifa de adquision de productos.
 */
function wp_ext_paypal_title_val_acq () { 	
	return get_option('wp_pp_payment_item1');	
}
/**
 * Shortcode: wp_ext_paypal_title_tax_iva *
 * Muestra el text descriptivo de la taxa de IVA.
 */ 
function wp_ext_paypal_title_tax_iva () { 	
	return get_option('wp_pp_payment_item2');	
}
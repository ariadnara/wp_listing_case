<?php
class Acquisition_Reserved {
    /*
     * Deuelve el total de bonos de tipo comodín (adquisiciones reservadas) del usuario activo. 
     */
    public static function wp_ext_total_acquisition_reserved() {
        global $wpdb;
        $query = "SELECT count(id) as count_reserved FROM wpda_ext_acq_reserved WHERE id_user = %d";
        $count_acq_reserved = $wpdb->get_results($wpdb->prepare( $query,get_current_user_id()), ARRAY_A); 
       return (!empty($count_acq_reserved) && isset($count_acq_reserved)) ? $count_acq_reserved[0]['count_reserved'] : 0;
     }
     /*
     * Permite insertar un bono de tipo comodín al usuario activo
     */
    public static function wp_ext_insert_acq_reserv($value_pay){
        global $wpdb;
        
         //determinando valor del impuesto IVA
        $impuesto_iva = get_option('wp_pp_payment_value2');
        $impuesto_iva = (empty($impuesto_iva)) ? 0 : $impuesto_iva;       
        $import_iva = (($impuesto_iva/100) * $value_pay);
        $import_pay = $value_pay + $import_iva;
    
        return $wpdb->insert('wpda_ext_acq_reserved', 
                            array('value_acq_reserved' => $import_pay,
                                  'id_user' => get_current_user_id()
                                  ), 
                            array( '%d','%d') 
                        );        
    }
    /**
     * Devuelve los bonos de tipo comidín activos del usuario (no utilizadas).
    */ 
    public static function wp_ext_acquisition_reserved_user_active() {
        global $wpdb;
        $query = "SELECT GROUP_CONCAT(id) as id_acq_reserved FROM wpda_ext_acq_reserved WHERE id_user = %d and status_acq_reserved = %d";
        $acq_reserved = $wpdb->get_results($wpdb->prepare( $query,get_current_user_id(),0), ARRAY_A); 
    return (!empty($acq_reserved) && isset($acq_reserved)) ? $acq_reserved[0]['id_acq_reserved'] : array();
    }
    /**
     * Marca como consumida la adquisicion reservada(comodin) si esa aun no se ha consumido.
    */ 
    public static function wp_ext_update_acq_status($id_acq, $status, $id_post_adq, $id_current_user, $where_status = 1){
        global $wpdb;
        $msg = array();
        $row_update = $wpdb->update('wpda_ext_acq_reserved', 
								  array('status_acq_reserved'=>$where_status,'id_post_ref'=>$id_post_adq),
								  array('id_user'=>$id_current_user, 'status_acq_reserved'=>$status, 'id'=>$id_acq),
								  array('%d','%d'),
								  array('%d','%d','%d'));
						   if($row_update == 0) {
                                $msg['result'] = false;
							    $msg['msg'] = "<b>No fue posible efectuar el pago por comod&iacute;n del producto seleccionado.</b>";	
						   } else {
                                $msg['result'] = true;
                                $msg['msg'] = "<b>Descontado comod&iacute;n de reserva satisfactoriamente.</b>";
                           }
        return $msg;
    }
    /**
     * Marca como activo un comidin.
    */ 
    public static function wp_ext_update_acq_status_active(){
        global $wpdb;
        $query = "UPDATE wpda_ext_acq_reserved SET status_acq_reserved = %d WHERE status_acq_reserved = %d  ORDER BY data_adq_pay ASC LIMIT %d;";
        return $wpdb->query($wpdb->prepare($query, 0, 1, 1));
    }

}

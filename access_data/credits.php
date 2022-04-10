<?php

class Credits {
    /**
     * Devuelve un array o numeric con el total de créditos comprados y creditos activos del usuario o un valor en especifico
     * según parametro:
     *  $get_value = 'arr_total' DEFAULT Devuelve un array simple en cada posicion los valores  array('limit_credit','sum_credits','count_acq_credits','sum_acq_credits');
     * $get_value = 'limit_credit' Devuelve la cantidad maxima de adquisiciones por credito.
     * $get_value = 'sum_credits' Devuelve el importe total de creditos comprado.
     * $get_value = 'count_acq_credits' Devuelve la cantidad de adquisiciones a creditos consumidos.
     * $get_value = 'sum_acq_credits' Devuelve el importe los creditos consumidos.
     * $get_value = 'credit_active' Devuelve el importe los creditos activos.
     * $get_value = 'acq_credit_pending' Devuelve las adquisiciones a creditos pendientes a consumir.
     */    
    public static function wp_ext_array_credits_actives($get_value = 'arr_total') {
        global $wpdb;
        $arr_result = array(0,0,0,0);
        $arr_index = array('limit_credit','sum_credits','count_acq_credits','sum_acq_credits');
           
        $query = "SELECT get_credits (%d) AS result";
        $result = (empty(get_current_user_id())) ? '': $wpdb->get_results($wpdb->prepare( $query,get_current_user_id()), ARRAY_A);
 
         if(!empty($result[0]['result']) && isset($result[0]['result'])){
             $arr_values = explode('|', $result[0]['result']); 	   
            $arr_result = array_combine($arr_index,$arr_values);
         }else  $arr_result = array_combine($arr_index,$arr_result);
             
         switch($get_value){
             case 'arr_total': return $arr_result;
             case 'limit_credit': return $arr_result['limit_credit'];
             case 'sum_credits': return $arr_result['sum_credits'];
             case 'count_acq_credits': return $arr_result['count_acq_credits'];
             case 'sum_acq_credits': return $arr_result['sum_acq_credits'];
             case 'credit_active':return $arr_result['sum_credits'] - $arr_result['sum_acq_credits'];
             case 'acq_credit_pending': return $arr_result['limit_credit'] - $arr_result['count_acq_credits']; 
         }
       return false; 
     }
      /*
     * Permite insertar un credito al usuario activo
     */
    public static function wp_ext_insert_credit($value_pay,$id_type,$limit_acq){
        global $wpdb;
        //determinando valor del impuesto IVA
        $impuesto_iva = get_option('wp_pp_payment_value2');
        $impuesto_iva = (empty($impuesto_iva)) ? 0 : $impuesto_iva;       
        $import_iva = (($impuesto_iva/100) * $value_pay);
        $import_pay = $value_pay + $import_iva;

        return $wpdb->insert('wpda_ext_credits', 
                            array('value_credit' => $import_pay,
                                  'id_user' => get_current_user_id(),
                                  'id_type_pkg_credit' => $id_type,
                                  'limit_acq' => $limit_acq
                                  ), 
                            array( '%d','%d','%d','%d') 
                        );        
    }

    }

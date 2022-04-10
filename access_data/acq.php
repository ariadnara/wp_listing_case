<?php

class Acquisition
{
    /**
     * Permite verificar si la adquisicion se encuentra pendiente de confirmacion teniendo en cuenta que la fecha de inserción sea mayor a 10 días
     */
    public static function wp_ext_product_reserved($post_id)
    {
        global $wpdb;
        $query = "SELECT count(id) AS prod_reserved FROM wpda_ext_acq WHERE post_id = %d";
        $prod_reserved = $wpdb->get_results($wpdb->prepare($query, $post_id), ARRAY_A);
        return (!empty($prod_reserved) && isset($prod_reserved)) ? $prod_reserved[0]['prod_reserved'] : 0;
    }
    /**
     * Permite obtener los datos mostrados (html) al usuario al adquirir un producto siempre este se encuentre en su periodo de reserva.
     * @param Integer $post_id
     * @param Integer $id_user
     * @return String HMTL
     */
    public static function wp_ext_get_data_reservation($post_id,$id_user)
    {
        global $wpdb;
        $query = "SELECT data_acquisition FROM wpda_ext_acq WHERE post_id = %d AND id_user_acq = %d";
        $prod_reserved = $wpdb->get_results($wpdb->prepare($query, $post_id, $id_user), ARRAY_A);
        return (!empty($prod_reserved) && isset($prod_reserved)) ? $prod_reserved[0]['data_acquisition'] : '';
    }
    /**
     * Devuelve las adquisiciones permitidas a consumir del usuario activo teniendo en cuenta las donaciones confirmadas y los comodines activos.
     */
    public static function wp_ext_acquisition_allow()
    {
        global $wpdb;
        $query = "SELECT get_acquisition_allow (%d) AS result";
        $count_acq_allow = $wpdb->get_results($wpdb->prepare($query, get_current_user_id()), ARRAY_A);
        return (!empty($count_acq_allow) && isset($count_acq_allow)) ? $count_acq_allow[0]['result'] : 0;
    }
    /**
     * Devuelve las donaciones que a confirmado el usuario activo para determinar las adquisiones que se le habilitarán a este. 
     */
    public static function wp_ext_acquisition_confirmed()
    {
        global $wpdb;
        $query = "SELECT count(*) as result
                 FROM wpda_ext_acq 
                 LEFT JOIN wp_posts ON wp_posts.ID = wpda_ext_acq.post_id
                 WHERE wp_posts.post_author = %d
                 AND post_type = %s
                 AND status_acq > %d";
        $count_acq_conf_x_user = $wpdb->get_results($wpdb->prepare($query, get_current_user_id(), 'hp_listing', 0), ARRAY_A);
        return (!empty($count_acq_conf_x_user) && isset($count_acq_conf_x_user)) ? $count_acq_conf_x_user[0]['result'] : 0;
    }
    /**
     * Devuelve si existe una adquisición se encuentra confirmada o el periodo de reserva (10 días posteriores a su reservación)
     */
    public static function wp_ext_exit_acq($post_id)
    {
        global $wpdb;
        $query = "SELECT count(id) AS exit_acq FROM wpda_ext_acq WHERE post_id = %d AND ((DATE_ADD(date_insert,INTERVAL 10 DAY)>=CURDATE() AND status_acq = %d) OR status_acq = %d )";
        $prod_reserved = $wpdb->get_results($wpdb->prepare($query, $post_id, 0, 1), ARRAY_A);
        return (!empty($prod_reserved) && isset($prod_reserved)) ? $prod_reserved[0]['exit_acq'] : 0;
    }
    /**
     * Actualiza el estado de una adquisición existente a adquisición consumida por donación.
     */
    public static function wp_ext_update_acq_consumed($id_user, $ref_id_acq_cierre)
    {
        global $wpdb;
        $query = "UPDATE wpda_ext_acq SET status_acq = %d, ref_id_acq_cierre = %d WHERE status_acq = %d AND post_id in (SELECT ID FROM wp_posts WHERE post_author = %d) ORDER BY date_insert ASC LIMIT %d;";
        $consumed = $wpdb->query($wpdb->prepare($query, 2, $ref_id_acq_cierre, 1, $id_user, 1));
        return $consumed;
    }
    /*
    * Verifica si el código de producto pertenece a las donaciones realizadas por el usuario activo.
    */
    public static function wp_ext_exit_code($code)
    {
        global $wpdb;
        $query = "SELECT count(*) AS result FROM wpda_ext_acq LEFT JOIN wp_posts on wp_posts.ID = wpda_ext_acq.post_id WHERE wpda_ext_acq.id_product_acq = %s AND wp_posts.post_author = %d";
        $count_code = $wpdb->get_results($wpdb->prepare($query, $code, get_current_user_id()), ARRAY_A);
        return (!empty($count_code) && isset($count_code)) ? $count_code[0]['result'] : 0;
    }
    /*
    * Insertar adquisicion
    */
    public static function wp_ext_insert_acq($post_id, $id_user,$origin,$value_acq){
        global $wpdb;
         $wpdb->insert( 'wpda_ext_acq', 
						array('post_id' => $post_id,
							  'id_user_acq' => $id_user,
							  'origin' => $origin,
							  'value_acq'=> $value_acq), 
						array( '%d','%d','%s','%d') 
					);
        return $wpdb->insert_id;
    }
    /*
    * Modificar column data_acquisition  
    */
    public static function wp_ext_update_column_data_acq($id_acq, $data_acq_html = ''){
        global $wpdb;
        return $wpdb->update('wpda_ext_acq', 
								  array('data_acquisition'=>$data_acq_html),
								  array('id_product_acq'=>$id_acq),
								  array('%s'),
								  array('%d'));
    }
    /*
    * Modificar estado de adquisicion  
    */
    public static function wp_ext_update_acq($status,$code_confirm){
        global $wpdb;
        return $wpdb->update('wpda_ext_acq', 
								  array('status_acq'=>$status,'data_closed'=>date("Y-m-d")),
								  array('id_product_acq'=>$code_confirm),
								  array('%d','%s'),
								  array('%s'));
								 
    }
}

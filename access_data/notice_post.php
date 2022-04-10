<?php
class Notice_Post
{
    /**
     * Permite obtener los contactos asociados al anunciante de un post o anuncio
     */
    public static function wp_ext_contact_data($id_post)
    {
        global $wpdb;
        $query = "SELECT id_product_acq, post_title, display_name, post_content, user_email, GROUP_CONCAT(meta_value ORDER BY meta_key ASC SEPARATOR %s) AS contact_data FROM wp_posts INNER JOIN wpda_ext_acq ON wp_posts.ID = wpda_ext_acq.post_id INNER JOIN wp_users ON wp_users.ID = wp_posts.post_author JOIN wp_usermeta ON wp_usermeta.user_id = wp_users.ID WHERE wpda_ext_acq.post_id = %d AND meta_key in ('%s', '%s', '%s') ";
        $data = $wpdb->get_results($wpdb->prepare($query, '<->', $id_post, 'description', 'first_name', 'last_name'), ARRAY_A);
        return (!empty($data) && isset($data)) ? $data : array();
    }
    /**
     * Devuelve el id de adquisicion según idpost e id del usuario que solicita.
     */
    public static function wp_ext_get_id_acq($post_id, $id_user)
    {
        global $wpdb;
        $query = "SELECT id FROM wp_posts WHERE ID = %d AND  post_author= %d";
        $id_acq = $wpdb->get_results($wpdb->prepare($query, $post_id, $id_user), ARRAY_A);
        var_dump($id_acq);
        return (!empty($id_acq) && isset($id_acq)) ? $id_acq[0]['id'] : 0;
    }
    /*
    * Actualiza el estado del post a draft para marcarlo como anuncio confirmado y cerrado. 
    */
    public static function wp_ext_update_post_confirm($code_confirm)
    {
        global $wpdb;
        $query = "UPDATE wp_posts set post_status = %s where post_type = %s and ID = (SELECT post_id FROM wpda_ext_acq WHERE id_product_acq = %s); ";
        $consumed = $wpdb->query($wpdb->prepare($query, 'draft', 'hp_listing', $code_confirm));
        return $consumed;
    }
    /*
    * Devuelve la cantidad total de anuncios publicados sin importar el post_status.
    */
    public static function wp_ext_count_user_posts($id_user, $post_type)
    {
        global $wpdb;
        $query = "SELECT count(*) AS result FROM wp_posts WHERE post_author = %d AND post_type = %s";
        $count_user_posts = $wpdb->get_results($wpdb->prepare($query, $id_user, $post_type), ARRAY_A);
        return (!empty($count_user_posts) && isset($count_user_posts)) ? $count_user_posts[0]['result'] : 0;
    }
    /*
     * Permite insertar el meta_post hp_reservado para identificar el anuncio como reservado al usuario final
     */
    public static function wp_ext_insert_post_as_reserved($post_id){
        global $wpdb;
        $error_msg = '';	
        return $wpdb->insert('wp_postmeta', 
                            array('meta_key' => 'hp_reservado',
                                  'meta_value' => 'RESERVADO',
                                  'post_id' => $post_id
                                  ), 
                            array( '%s','%s','%d') 
                        );       
    }
}

<?php

class Type_Credits {

    public static function get_limit_acq_reserved($id){
        global $wpdb;
        $query = "SELECT limit_acq FROM wpda_ext_type_pkg_credit WHERE id = %d AND type_credit = %d";
        $limit_acq = $wpdb->get_results($wpdb->prepare($query, $id, 1), ARRAY_A);
        return (!empty($limit_acq) && isset($limit_acq)) ? (int)$limit_acq[0]['limit_acq'] : 0;
    }
    public static function get_data_type_credit($id, $type_credit){
        global $wpdb;
        $query = "SELECT limit_acq, value FROM wpda_ext_type_pkg_credit WHERE id = %d AND type_credit = %d";
        $limit_acq = $wpdb->get_results($wpdb->prepare($query, $id, $type_credit), ARRAY_A);
        return (!empty($limit_acq) && isset($limit_acq)) ? $limit_acq[0] : array(0,0);
    }
    public static function get_data_payment($user, $date_from,$date_to, $type_pay,$limit,$page_ini){
        global $wpdb;
        $query = "SELECT * FROM _getDataPaymment";
        $pagination = '';
        
        if(!empty($user)){
            $where[] = 'user_login like %s'; 
            $prepare_where[] = $user;
        }  
        if(!empty($date_from)){
            $where[] = '_date > %s';
            $prepare_where[] = $date_from; 
        } 
        if(!empty($date_to)){
            $where[] = '_date < %s';
            $prepare_where[] = $date_to; 
        }             
        if(!empty($type_pay)){
            $where[] = '_type collate utf8mb4_unicode_ci like %s';
            $prepare_where[] = $type_pay; 
        }         
        if(!empty($limit)){
            $pagination = ' LIMIT %d, %d';
            $prepare_where[] = $page_ini; 
            $prepare_where[] = $limit; 
        } 
        if(!empty($where) && isset($where)){            
            $where = ' WHERE '.implode(' AND ', $where);      
        }else  $where = '';

        if(!empty($where) || !empty($pagination))
            $_prepare = $wpdb->prepare($query.$where.$pagination, $prepare_where);
        else  $_prepare = $wpdb->query($query);
        
        $limit_pos = strpos($_prepare,'LIMIT'); 
        if($limit_pos !== false)            
            $_prepare_without_limit = substr($_prepare, 0, $limit_pos);
         
        $_prepare_without_limit = str_replace('*', 'COUNT(*) AS total', $_prepare_without_limit);   
        
        $data_result['data'] = $wpdb->get_results($_prepare, ARRAY_A);        
        $data_result['count'] = $wpdb->get_results($_prepare_without_limit, ARRAY_A);
              
        return $data_result;
    }      
}

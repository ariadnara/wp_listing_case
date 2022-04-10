<?php

require_once __DIR__.'/access_data/notice_post.php';
require_once __DIR__.'/access_data/acq.php';
require_once __DIR__.'/access_data/acq_reserved.php';
require_once __DIR__.'/access_data/credits.php';
require_once __DIR__.'/access_data/type_pkg_credit.php';
/**
 * Auxiliary function: auxiliary_fn_insert_comodines *
 * Gestionando insercion de comodin si este acaba de ser comprado.
 */
function auxiliary_fn_insert_comodin($total_acq_reserved){
	$row_insert = 0;
	$param_orgn_exist = (array_key_exists('orgn',$_GET) && !empty($_GET['orgn'] ) );
	$param_i_exist = (array_key_exists('i',$_GET) && !empty($_GET['i'] ) );
	$param_p_exist = (array_key_exists('e',$_GET) && !empty($_GET['e'] ) ); //credit_active encriptado	
	
	 if($param_orgn_exist && $param_i_exist && $param_p_exist){ 
		$paramencrypted = md5(get_current_user_id().$total_acq_reserved);	
		$paramencryted_aux = md5($total_acq_reserved); 
		$id_limit_acq = (int)$_GET['i'];	
		if($_GET['orgn'] == $paramencrypted && $total_acq_reserved < 2 && $id_limit_acq > 0 && $_GET['e']== $paramencryted_aux){
			$type_data = Type_Credits::get_data_type_credit($id_limit_acq, 1);
			$insert_max = $type_data['limit_acq'];
			$value_pay = $type_data['value'];

			while($insert_max != 0){
				$result = Acquisition_Reserved::wp_ext_insert_acq_reserv($value_pay);
				$row_insert += ($result === false) ? 0 : $result;
				$insert_max--;				
			}
			$row_insert = ($row_insert == 0) ? -1 : $row_insert;
		}
	}return $row_insert;
}
/**
 * Auxiliary function: auxiliary_fn_insert_credits *
 * Gestionando inserción del crédito si este acaba de ser comprado.
 */
function auxiliary_fn_insert_credits($credit_active){	
	$param_orgn_exist = (array_key_exists('orgn',$_GET) && !empty($_GET['orgn'] ) );
	$param_i_exist = (array_key_exists('i',$_GET) && !empty($_GET['i'] ) ); //id del tipo de credito
	$param_p_exist = (array_key_exists('e',$_GET) && !empty($_GET['e'] ) ); //credit_active encriptado
	$row_insert = 0;
	
	 if($param_orgn_exist && $param_i_exist && $param_p_exist){ 
		$paramencrypted = md5(get_current_user_id().$credit_active);
		$paramencryted_aux = md5($credit_active); 
		$id_limit_acq = (int)$_GET['i'];	
		if($_GET['orgn'] == $paramencrypted && $id_limit_acq > 0 && $_GET['e']== $paramencryted_aux){
			$type_data = Type_Credits::get_data_type_credit($id_limit_acq, 0);
			$limit_acq = $type_data['limit_acq'];
			$value_pay = $type_data['value'];
			$row_insert = Credits::wp_ext_insert_credit($value_pay,$id_limit_acq,$limit_acq);
			$row_insert = ($row_insert === false) ? -1 : $row_insert;
		}
	}return $row_insert;
}
/**
 * Shortcode: wp_ext_show_txt_welcome *
 * Muestra lo que puede hacer el usuario atendiendo a las adquisiciones permitidas, credito activo y comodines comprados.
 */
function wp_ext_show_txt_welcome () {
   global $current_user;
   $pay_free = get_option('_active_mode_free');
   $current_display_name = $current_user->display_name;

   if (empty($current_display_name)){
	$text_welcome = "Hola <b>amigo visitante</b> debes registrarte (click bot&oacute;n acceder) y a&ntilde;adir al menos un anuncio para comenzar a adquirir productos.";
   	return "<p style='text-align:center'>{$text_welcome}</p>";
   } 

   if ($pay_free == 1){		
		$text_welcome = "Hola <b>{$current_display_name}</b> para comenzar a adquirir productos debes a&ntilde;adir al menos un anuncio.";
   	return "<p style='text-align:center'>{$text_welcome}</p>";
   }      
       
   $allow_acq = Acquisition::wp_ext_acquisition_allow();
   $credit_active = Credits::wp_ext_array_credits_actives('acq_credit_pending');
   $total_acq_reserved = Acquisition_Reserved::wp_ext_total_acquisition_reserved();
   $text_welcome = '';	
   $result_insert_comodin = (auxiliary_fn_insert_comodin($total_acq_reserved) > 0) ? 1 : 0;;
   $result_insert_credits =	(auxiliary_fn_insert_credits($credit_active) > 0) ? 1 : 0;
   $total_acq_reserved += $result_insert_comodin;
   $credit_active += $result_insert_credits;
   	   
   if($result_insert_credits == -1 || $result_insert_comodin == -1){
		$msg_result_insert = "<p style='text-align:center; color:#FFD700'>";
		$msg_result_insert .= "No fue posible registrar la compra. Intente m&aacute;s tarde o cont&aacute;ctenos mediante el correo <b>contacto@reyganplus.es</b>.";
		$msg_result_insert .= "</p>";
	}  	
   //msg adquisiciones pendientes
   if($allow_acq > 0) 
       $msg_allow_acq = "puedes adquirir <b>{$allow_acq} producto(s) </b>.<br>";
   else
       $msg_allow_acq = "para adquirir productos debes confirmar una donaci&oacute;n introduciendo el c&oacute;digo previamente generado.";
   //msg creditos activos	
   if($credit_active > 0){
       $text_acq = ($credit_active == 1) ? "adquisici&oacute;n" : "adquisiciones";
       $msg_credit_active = "Tienes reservado a cr&eacute;ditos <b>$credit_active $text_acq</b>.";	
   }
       else $msg_credit_active = '';
   //msg comodines
   if ($total_acq_reserved == 1)
       $msg_acq_reserved = "Puedes comprar un comod&iacute;n para adquirir un producto sin hacer una donaci&oacute;n previa.";
   elseif ($total_acq_reserved == 0)
       $msg_acq_reserved = "Te regalamos la compra de hasta dos comodines para adquirir productos sin donaciones previas.";
   else $msg_acq_reserved = '';
   //msg de bienvenida   
       $msg_name_user = "Hola, <b>{$current_user->display_name}</b>";
       $text_welcome = $msg_name_user.', '.$msg_allow_acq.' '.$msg_acq_reserved.' '. $msg_credit_active;
   

   $out = "<p style='text-align:center'>{$text_welcome}</p>";
   $out.= $msg_result_insert;
   return $out;
} 
/**
 * Auxiliary function: auxiliary_fn_get_data_contact *
 * Devuelve datos de contacto
 */
function auxiliary_fn_get_data_contact($id_post_adq){

	try{
		$contact_post = Notice_Post::wp_ext_contact_data($id_post_adq);
		if(empty($contact_post) && !isset($contact_post[0]))
			throw new Exception("Un error de conexi&oacute;n impide mostrar los datos de contacto del donante.");

			
			$id_product_acq = $contact_post[0]['id_product_acq'];
			$contact_data = $contact_post[0]['contact_data'];
					
		if(!empty($contact_data)){
			$contact_data = explode('<->', $contact_data);
			$data_dir_tef = $contact_data[0];
			$first_name = $contact_data[1];
			$last_name = $contact_data[2];
			
			if(!empty($data_dir_tef)){
				$data_dir_tef = explode('|', $data_dir_tef);
				$direccion = $data_dir_tef[0];
				$cod_postal = $data_dir_tef[1];
				$main_telef = $data_dir_tef[2];
				$second_telef = (!empty($data_dir_tef[3])) ? $data_dir_tef[3] : 'No definido por el donante';
				$hr_main_telef = $data_dir_tef[4];
				$hr_second_telef = (!empty($data_dir_tef[3])) ? $data_dir_tef[5] : 'No definido por el donante';
			}
		}
			$out = '<p style="font-size:13px;">';	
			$out .= "<b>CODIGO DEL PRODUCTO: </b>{$id_product_acq}.";
			$out .= "<br><b>PRODUCTO: </b>{$contact_post[0]['post_title']}.";
			//$out .= "<br><b>DESCRIPCION DEL PRODUCTO:</b>{$result[0]['post_content']}";
			$out .= "<br><b>NOMBRE DEL DONANTE:</b> $first_name $last_name ({$contact_post[0]['display_name']}).";
			$out .= "<br><b>TELEFONOS PRINCIPAL:</b> $main_telef";
			$out .= "<br><b>HORA DE CONTACTO (TELEFONO PRINCIPAL):</b> $hr_main_telef";
			$out .= "<br><b>TELEFONOS SECUNDARIO:</b> $second_telef";				
			$out .= "<br><b>HORA DE CONTACTO (TELEFONO SECUNDARIO):</b> $hr_second_telef";
			$out .= "<br><b>DIRECCION:</b> $direccion";
			$out .= "<br><b>CODIGO POSTAL:</b> $cod_postal";
			$out .= "<br><b>CORREO ELECTRONICO:</b>{$contact_post[0]['user_email']}";
			$out .= "</p>";

			$update_html_data = Acquisition::wp_ext_update_column_data_acq($id_product_acq, $out);
			if($update_html_data === false)
				throw new Exception("Un error de conexi&oacute;n impide registrar los datos de la adquisici&oacute;n.");

			return $out;

		} catch(Exception $exception){
			return	$exception->getMessage();
		}
}
/**
 * Shortcode: wp_ext_get_url_acq *
 * Volver al anuncio luego de adquirir un producto.
 */
function wp_ext_get_url_acq(){	
	if(array_key_exists('id_padq',$_GET)){
		$id_post = $_GET['id_padq'];
		$post_acq = get_post($id_post);
		$back_url = get_home_url()."/listing/".$post_acq->post_name;
		return '<a class="hp-listing__action hp-listing__action--review hp-link" href="'.$back_url.'">Volver al anuncio</a>';
	}
}
/**
 * Shortcode: wp_ext_show_data_acq *
 * Muestra los datos de contacto del producto y del anunciante de la donacion
 */
function wp_ext_show_data_acq () {	
	global $post;	
	$out_open_p = "<p style='text-align:left'>";
	$out_close_p = "</p>";
	$last_msg = "Int&eacute;ntelo más tarde o cont&aacute;ctenos en el correo <b><contacto@reyganplus.es></b>.";
	$msg_aux = '';
	
	try{
		$exit_acq = Acquisition::wp_ext_exit_acq($post->ID);

		if( $exit_acq > 0
		 || !array_key_exists('id_padq',$_GET) 
		 || empty($_GET['id_padq']) 
		 || !array_key_exists('orgn',$_GET) 
		 || empty($_GET['orgn']))
		throw new Exception("Un error de conexi&oacute;n impide registrar el producto como adquirido.");

		$id_post_adq = $_GET['id_padq'];
		$origin_acq = $_GET['orgn'];
		$id_current_user = get_current_user_id();
		$value_acq = get_option('wp_pp_payment_value1');	
		
		//$wpdb->query( "START TRANSACTION" );

		switch($origin_acq){
			case 0: $origin = 'pay';break;
			case 1: {
					$origin = 'reserved'; 
					$str_acq_reserved = Acquisition_Reserved::wp_ext_acquisition_reserved_user_active();
					$arr_acq_reserved = explode(',',$str_acq_reserved);
					$id_adq_reserved = $arr_acq_reserved[0];
				
					if( !empty($id_adq_reserved) && isset($id_adq_reserved)){
						$msg_out = Acquisition_Reserved::wp_ext_update_acq_status($id_adq_reserved, 0, $id_post_adq, $id_current_user);
						
						if($msg_out['result'] === false )
							throw new Exception($msg_out['msg']);
						
							$msg_aux = $msg_out['msg'];
					} 	
			}break;
			case 2: $origin = 'credit'; break;
			default: 'pay';		
		}
		//Registrando adquision, registrandola como RESERVADA y consumida
		$insert_acq = Acquisition::wp_ext_insert_acq($id_post_adq, $id_current_user,$origin,$value_acq); 
		if($insert_acq === false) 
			throw new Exception('Adquisici&oacute;n no registrada.');
		
		$insert_post_meta = Notice_Post::wp_ext_insert_post_as_reserved($id_post_adq); 
		if($insert_post_meta === false) 
			throw new Exception('Adquisici&oacute;n registrada pero no marcada como RESERVADA.');
	
		if($origin_acq != 1){
				$update_acq = Acquisition::wp_ext_update_acq_consumed($id_current_user, $insert_acq);			
				if($update_acq === false)
					throw new Exception("Adquisici&oacute;n no descontada de las estad&iacute;sticas del usuario.");
		}
		//Obteniendo datos para mostrar
		$contact_post = auxiliary_fn_get_data_contact($id_post_adq);
		if(empty($contact_post))
			throw new Exception("Un error de conexi&oacute;n impide mostrar los datos de contacto del donante.");
		
		//$wpdb->query( "COMMIT" );

		return $out_open_p.$contact_post.$msg_aux.$out_close_p;
		
	}catch(Exception $excepcion){
		return $out_open_p.$excepcion->getMessage().$last_msg.$out_close_p;
		//$wpdb->query( "ROLLBACK" );
	}
}
/**
 * Shortcode: wp_ext_shortcode_confirm_code *
 * Muestra texto shortcode al confirmar la donación de un producto.
 */
function wp_ext_shortcode_confirm_code() { 
	global $wpdb; $out = '';	
	$id_current_user = get_current_user_id();	
	$out_open_p_ok = "<p style='text-align:center; color: #239b56;'>";
	$out_open_p_error = "<p style='text-align:center;color: #78281f;'>";
	$out_close_p = "</p>";
	$msg_aux = "";
	
	if($id_current_user == 0){
		return "<script> window.location.replace(".json_encode(get_home_url())."+'/account/login/');</script>";
	}	
	if(array_key_exists('code_confirm',$_GET) && !empty($_GET['code_confirm']) ){
		$code_confirm = $_GET['code_confirm'];
		$exit_code = Acquisition::wp_ext_exit_code($code_confirm);
		
		if($exit_code > 0){				
			$row_update = Acquisition::wp_ext_update_acq(1, $code_confirm);
		if($row_update === false || $row_update == 0 ) 
			$out = $out_open_p_error."El producto se encuentra confirmado previamente.".$out_close_p;
		else {
			$result_update = Notice_Post::wp_ext_update_post_confirm($code_confirm);				
				if($result_update === false || $result_update == 0){				 						   
				   $out = "No fue posible registrar la traza de la confirmaci&oacute;n del producto.";	
				  return $out_open_p_error.$out.$out_close_p;
				}
			$update_status_active = Acquisition_Reserved::wp_ext_update_acq_status_active();			
			if($update_status_active === false){				 						   
				$out = "No fue posible compensar el comod&iacute;n.";	
			   return $out_open_p_error.$out.$out_close_p;
			 }else {
				 if($update_status_active > 0)
				 	$msg_aux = 'Ha compensado un comod&iacute;n.';
				}

			$out = $out_open_p_ok."El c&oacute;digo confirmado correctamente. $msg_aux".$out_close_p;
			}
		}else 
			$out = "<p style='text-align:center; color: #78281f;'>El c&oacute;digo introducido no corresponde a los productos donados por usted.</p>.";
	}
	return $out;	
} 
/**
 * Shortcode: wp_ext_shortcode_get_table_payment *
 * Muestra table con detalles de venta.
 */
function wp_ext_shortcode_get_table_payment() { 
	global $current_user, $wp;  
	$wp->add_query_var('page_table');
	$permisos = $current_user->get_role_caps();
	$out_open_p_error = "<div class='div_msg'><p class='error_msg'>";
	$out_close_p = "</p></div>";	

	try{
		if($permisos['manage_options'] !== true)
			throw new Exception($out_open_p_error."Usted no presenta permisos para acceder a esta opci&oacute;n.".$out_close_p);
		
		$user = (array_key_exists('user',$_GET) && !empty($_GET['user']) ) ? $_GET['user'] : '';
		$fecha_ini = (array_key_exists('fecha_ini',$_GET) && !empty($_GET['fecha_ini']) ) ? $_GET['fecha_ini'] : '';
		$fecha_fin = (array_key_exists('fecha_fin',$_GET) && !empty($_GET['fecha_fin']) ) ? $_GET['fecha_fin'] : '';
		$type_pay = (array_key_exists('type_pay',$_GET) && !empty($_GET['type_pay']) ) ? $_GET['type_pay'] : '';
		$limit = (array_key_exists('limit',$_GET) && !empty($_GET['limit']) ) ? $_GET['limit'] : 15;
		$page = (array_key_exists('page_table',$_GET) && !empty($_GET['page_table']) ) ? $_GET['page_table'] : 1;
		$row_ini = round(($page - 1) * $limit); 

		if(empty($user) && empty($type_pay) && empty($fecha_ini) && empty($fecha_fin))
			throw new Exception($out_open_p_error."Debe especificar el usuario, la fecha de inicio y fin para comenzar la b&uacute;squeda.".$out_close_p);
	
		if((empty($fecha_ini) && !empty($fecha_fin)) || (!empty($fecha_ini) && empty($fecha_fin))) 
			throw new Exception($out_open_p_error."Debe especificar la fecha de inicio y fin de la b&uacute;squeda.".$out_close_p);
		
		$data_result = Type_Credits::get_data_payment($user, $fecha_ini, $fecha_fin, $type_pay, $limit, $row_ini);
		$data_payment = $data_result['data'];
		$data_count = $data_result['count'][0]['total']; 
		$total_pages =  ($data_count > $limit) ? ceil($data_count / $limit) : 1;
		
		if(empty($data_payment) || !isset($data_payment))
			throw new Exception($out_open_p_error."No existen registros para el criterio de b&uacute;squeda especificado.".$out_close_p);
		
			$col_hidden = array('id_user');
			$table_head = array(
				'_no'=>'#',
				'_type'=>'Tipo de venta',
				'_date'=>'Fecha',
				'_value'=>'Importe',
				'user_login'=>'Usuario de registro',
				'display_name'=>'Nombre de usuario',				
				'notes'=>'Notas'
			);
			$url_page = get_home_url()."/listado-ventas/?user=$user&type_pay=$type_pay&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&page_table=";			
			return auxiliary_fn_get_table_by_page($table_head, $data_payment, $col_hidden, $total_pages,$url_page,$page,$row_ini);	

	}catch(Exception $exe){
			return $exe->getMessage();
	}	
}

function auxiliary_fn_get_table_by_page($table_head, $data_show, $col_hidden, $total_pages,$url_page,$page,$row_ini){
		//table	
		$table = '<table class="hp-listings hp-block hp-table hp-table-last-col">';
		$table_end = '</table>';
		$str_hds = '';		

		foreach ($table_head as $value) {
			$str_hds.= '<th>'.$value.'</th>';
		}
		$str_trs = '';
		foreach ($data_show as $data) {
			$row_ini++;
			$str_tds = '<td><b>'.$row_ini.'</b></td>';			
			foreach ($data as $col => $val) { 
				if(array_search($col, $col_hidden) === false) 
					$str_tds .= '<td>'.$val.'</td>';
			}
			$str_trs .= '<tr>'.$str_tds.'</tr>';			
		}		
		//Paginacion
		$nav = '<nav class="hp-pagination navigation pagination nav-links">';
		$nav_end = '</nav>';		
		$pages = '';
		$page_number = 1;
		$total_show_pages = 3;

		//Limitando cantidad de p'agina a mostrar para evitar desbordamiento visual.
      	if($total_pages > $total_show_pages && $page > $total_show_pages){
			$page_number = ($page - $total_show_pages + 1) ;
			$max_page_showed = $page;
		}else $max_page_showed = $total_show_pages;

		while($page_number < $max_page_showed+1){
			if($page_number == $page)
				 $pages .= '<span class="page-numbers current" href="'.$url_page.$page_number.'">'.$page_number.'</span>';
			else $pages .= '<a class="page-numbers" href="'.$url_page.$page_number.'">'.$page_number.'</a>';			 
		   $page_number++;
		} 
		if($total_pages > 2){
			$page_prev = $page - 1;
			$page_next = $page + 1; 
			$prev = '<a class="prev page-numbers" href="'.$url_page.$page_prev.'">'.$page_number.'</a>';			
			$next = '<a class="next page-numbers" href="'.$url_page.$page_next.'">'.$page_number.'</a>';
			$start = '<a class="prev page-numbers" href="'.$url_page.'1">'.$page_number.'</a>';			
			$end = '<a class="next page-numbers" href="'.$url_page.$total_pages.'">'.$total_pages.'</a>';
			
			$pages = $start.$prev.$pages.$next.$end;		
	    }
		return $table.$str_hds.$str_trs.$table_end.$nav.$pages.$nav_end;		
}
/**
 * Shortcode: wp_ext_show_stadistic *
 * Muestra texto shortcode con la estad'istica de gestion de adquisiciones del usuario activo.
 */
function wp_ext_show_stadistic () {
	
	$total_post_user = Notice_Post::wp_ext_count_user_posts(get_current_user_id(),'hp_listing');
	$count_acq_conf_x_user = Acquisition::wp_ext_acquisition_confirmed();
	$allow_acq = Acquisition::wp_ext_acquisition_allow();
	$str_acq_reserved = Acquisition_Reserved::wp_ext_acquisition_reserved_user_active();
	$arr_acq_reserved = explode(',',$str_acq_reserved);	
	$count_acq_reserved = (!empty($arr_acq_reserved[0])) ? count($arr_acq_reserved) : 0;
	$acq_allow_conf = $allow_acq - $count_acq_reserved;
	$acq_allow_conf = ($acq_allow_conf < 0) ? 0 :$acq_allow_conf; 		
	$count_total_acq_rev = Acquisition_Reserved::wp_ext_total_acquisition_reserved();	
	$arr_dat_credits = Credits::wp_ext_array_credits_actives();	
	$acq_consumed = ($count_acq_conf_x_user - $acq_allow_conf);
	$acq_consumed = ($acq_consumed < 0) ? 0 : $acq_consumed;
	$acq_reserved_consumed = ($count_total_acq_rev - $count_acq_reserved);
	$acq_reserved_consumed = ($acq_reserved_consumed < 0) ? 0 : $acq_reserved_consumed;
	if(!empty($arr_dat_credits)){
		extract($arr_dat_credits);
		$sum_credits = (empty($sum_credits)) ? 0 : $sum_credits; 
		$sum_acq_credits = (empty($sum_acq_credits)) ? 0 : $sum_acq_credits; 
		$limit_credit = (empty($limit_credit)) ? 0 : $limit_credit; 
		$count_acq_credits = (empty($count_acq_credits)) ? 0 : $count_acq_credits; 		
		$adq_pending = $limit_credit - $count_acq_credits;
	}else {
		$adq_pending = 0;
		$sum_credits = 0;
		$sum_acq_credits = 0;
		$limit_credit = 0;
		$count_acq_credits = 0;			
	}	
	$span_etiq_open = '<span style="font-size:13px;">';
	$span_etiq_open_color = '<span style="font-size:13px; color:rgba(15,23,39,.85);">';
	$icon = '<i class="hp-icon fas fa-flag"></i>';
	$span_etiq_close = '</span>';
	$icon_list = '&#9737';
	
	$out = $span_etiq_open_color.$icon.'&nbsp;<b>Total de adquisiciones a consumir: '.$allow_acq. '</b>'. $span_etiq_close;
	$out .= '<br>';
	$out  .= $span_etiq_open. $icon_list.'&nbsp;<b>Anuncios publicados:</b> '.$total_post_user.$span_etiq_close;
	$out .= '<br>';
	$out .= '<br>';	
	$out .= $span_etiq_open.$icon_list.'&nbsp;<b>Donaciones confirmadas: '.$count_acq_conf_x_user.'</b>'.$span_etiq_close;	
	$out .= '<br>';	
	$out .=  $span_etiq_open.'&nbsp;&nbsp;&nbsp;Adquisiciones consumidas: '. $acq_consumed. $span_etiq_close;
	$out .= '<br>';
	$out .= $span_etiq_open_color.'&nbsp;&nbsp;&nbsp;<b>Adquisiciones disponibles: '.$acq_allow_conf.$span_etiq_close.'</b>'.$span_etiq_close;;
	$out .= '<br>';
	$out .= '<br>';
	$out .= $span_etiq_open.$icon_list.'&nbsp;<b>Comodines comprados:</b> '.$count_total_acq_rev. $span_etiq_close;
	$out .= '<br>';
	$out .= $span_etiq_open.'&nbsp;&nbsp;&nbsp;Comodines consumidos: '. $acq_reserved_consumed.$span_etiq_close;
	$out .= '<br>';
	$out .= $span_etiq_open_color.'&nbsp;&nbsp;&nbsp;<b>Comodines disponibles: '.$count_acq_reserved.'</b>'.$span_etiq_close;	
	$out .= '<br>';
	$out .= '<br>';
	$out .= $span_etiq_open. $icon_list.'&nbsp;<b>Cr&eacute;dito comprado:</b> '.$sum_credits.'€ </b>'.$span_etiq_close;
	$out .= '<br>';
	$out .= $span_etiq_open.'&nbsp;&nbsp;&nbsp;Adquisiciones cubiertas a cr&eacute;dito: '.$limit_credit.'</b>'.$span_etiq_close;	
	$out .= '<br>';
	$out .= $span_etiq_open.'&nbsp;&nbsp;&nbsp;Adquisiciones consumidas a cr&eacute;dito: '.$count_acq_credits.'</b>'.$span_etiq_close;
	$out .= '<br>';
	$out .= $span_etiq_open_color.'&nbsp;&nbsp;&nbsp;<b>Adquisiciones por consumir a cr&eacute;dito: '.$adq_pending.$p_etiq_close.'</b>'.$span_etiq_close;
	return $out;	
} 
/** Actions wp_head________________________________________________________________________________________________________ */
 
/**
 * Action wp_head: wp_ext_excess_reserve *
 * Alerta si el usuario ha excedido la cantidad de comodines permitido, para adquirir productos debe confirmar al menos una donación.
 */
function wp_ext_excess_reserve () {
    $current_post = get_post();
    $home_url = json_encode(get_home_url());
    $count_total_acq_rev = Acquisition_Reserved::wp_ext_total_acquisition_reserved();
       
    if($current_post->post_name == 'comprar-comodin' && $count_total_acq_rev == 2 )	{
   ?>
   <script>
       alert('Ha excedido la cantidad de comodines permitida, para adquirir productos debe confirmar al menos una donación.');
        window.location.replace(<?php echo $home_url;?>);
   </script>
   <?php }
}

/**
 * Action wp_head: reprogram_original_element_template *
 * A partir de la cantidad de adquisiones y comodines, activa o desactiva la opción de adquirir productos.
 */
function  reprogram_original_element_template() { 	
	global $post, $wp, $current_user; 
	
	//Determinando si el usuario se encuentra registrado
	$id_current_user = get_current_user_id(); 
//	if($id_current_user == 0)
//		return "<script> window.location.replace(".json_encode(get_home_url())."+'/account/login/');</script>";
	
	//Determinando si tienen comprado el total de comodines
	$current_post = get_post();
	$home_url = json_encode(get_home_url());
	$total_acq_reserved = Acquisition_Reserved::wp_ext_total_acquisition_reserved();	
	if($current_post->post_name == 'comprar-comodin' && $total_acq_reserved == 2 )	{
	?>
	<script>
		alert('Ha excedido la cantidad de comodines permitida, para adquirir productos debe confirmar al menos una donación.');
		window.location.replace(<?php echo $home_url;?>);
	</script>
	<?php }

	//Definiendo variables auxiliares
	$desc_user = get_user_meta($id_current_user, 'description'); 
	$arr_contact_user = explode('|', $desc_user[0]);
	unset($arr_contact_user[3]); //quitando telefono secundario
	unset($arr_contact_user[5]); //quitanto horario de telefono secundario
	$contact_user_is_null = (in_array('', $arr_contact_user) || in_array('undefined', $arr_contact_user)) ? 1 : 0;
	$allow_adq = 0;	
	$total_acq_reserved = 0;
	$count_acq_reserved = 0;
	$acq_credit_pending = 0;
	$prod_reserved = 0;
	$is_user_post = ($post->post_author == $id_current_user) ? 1 : 0;
    $post_id = $post->ID;
	$data_reservation = 0;
	$permisos = $current_user->get_role_caps();
	$is_admin = (array_key_exists('manage_options',$permisos) && !empty($permisos['manage_options']) ) ? $permisos['manage_options'] : '0';
	$pay_free = get_option('_active_mode_free');   
	$_use_credits = get_option('_use_credits');   
	$_use_acq_reserved = get_option('_use_acq_reserved'); 
	$listing_active = 0;  

	if($post->post_type == 'hp_listing'){		
		$allow_adq = Acquisition::wp_ext_acquisition_allow();		
		$str_acq_reserved = Acquisition_Reserved::wp_ext_acquisition_reserved_user_active();
		$arr_acq_reserved = explode(',',$str_acq_reserved);
		$count_acq_reserved = (!empty($arr_acq_reserved[0])) ? count($arr_acq_reserved) : 0;	
		$acq_credit_pending = Credits::wp_ext_array_credits_actives('acq_credit_pending');
		$prod_reserved = Acquisition::wp_ext_product_reserved($post->ID);
		$data_reservation = Acquisition::wp_ext_get_data_reservation($post->ID,$id_current_user);
		$data_reservation = empty($data_reservation) ? 0 : json_encode($data_reservation);
		$listing_active = Notice_Post::wp_ext_count_user_posts($id_current_user, 'hp_listing');		
	} 	
	$post_type = json_encode($post->post_type);
	
	if(get_query_var('id_adpq',0) != 0)  
		$wp->add_query_var('id_padq');	
	if(get_query_var('orgn',0) != 0)  
		$wp->add_query_var('orgn');	

	$dir_home = get_home_url();
	$dir_script = $dir_home.'/wp-content/themes/listinghive-child/js/hp_ext_acq.js';
	echo '<script src="'.$dir_script.'"></script>';
?>
<script>
	document.addEventListener('readystatechange', event => { 
      if (event.target.readyState === "complete") {	
		var post_type = <?php echo $post_type;?>,
		     data_reservation = <?php echo $data_reservation;?>,
			 contact_user_is_null = <?php echo $contact_user_is_null;?>,
			 home_url = <?php echo $home_url;?>,
			 prod_reserved = <?php echo $prod_reserved;?>,			
			 is_admin = <?php echo $is_admin;?>;			
		switch(post_type){
			case 'hp_listing': {
					var param = {};
						param.allow_adq = Number.parseInt(<?php echo $allow_adq;?>);
						param.home_url = home_url; 	
						param.acq_credit_pending = <?php echo $acq_credit_pending;?>;
						param.count_acq_reserved = <?php echo $count_acq_reserved;?>; 
						param.total_acq_reserved = <?php echo $total_acq_reserved;?>;
						param.prod_reserved = prod_reserved;
						param.id_current_user = <?php echo $id_current_user;?>;
						param.is_user_post = <?php echo $is_user_post;?>;
						param.pay_free = <?php echo $pay_free;?>;
						param._use_credits = <?php echo $_use_credits;?>;
						param._use_acq_reserved = <?php echo $_use_acq_reserved;?>;
						param.listing_active = <?php echo $listing_active;?>;
						param.post_id = <?php echo $post_id;?>;	
						active_btn_acq(param);	
						hide_secundaries_element_page_listing();

						if(data_reservation != 0)
							show_data_vender(data_reservation);
						else {
							hide_element_hivepress_by_class("hp-vendor__image");
							show_firts_character_name_vendor();
						}
			} break;
			case 'post': {
						form_field_update();
						hide_options_post_listing(prod_reserved);
						show_msg_error_data_contact_null(contact_user_is_null);
					
						if(is_admin == '1'){
							show_data_admin(home_url +'/listado-ventas/');}
			} break;						
		}
		//Comprobando que los datos obligatorios del anunciantes se encuentra completados.
		if(contact_user_is_null == 1){
			var btn_submit = document.getElementsByClassName("hp-menu__item hp-menu__item--listing-submit button button--secondary");
			btn_submit[0].setAttribute("data-url", home_url +'/account/settings/');
		}
	}
  });
</script>
<?php } ?>

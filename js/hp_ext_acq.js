//Activa funcionalidad de obtener adquisiciones
function active_btn_acq(param) {
    var adq_page = '',
        div_actions = document.getElementsByClassName('hp-listing__actions hp-listing__actions--primary hp-widget widget');

    if (div_actions.length > 0) {
        var btn = document.createElement("button");
        btn.setAttribute("class", "hp-listing__action hp-listing__action--message button button--large button--primary alt");
        btn.innerHTML = "<span>Obtener datos de contacto</span>";
        div_actions[0].appendChild(btn);
    
        if (param.prod_reserved > 0 
            || param.is_user_post == 1 
            || (param._use_acq_reserved == 1 && param.allow_adq === 0 && param.total_acq_reserved == 2)
            || param.listing_active === 0) {
            btn.style.background = "#bac2bd";
            if (param.prod_reserved > 0){
                btn.innerHTML = '<span>Producto reservado</span>';
                var a_menu_item = document.getElementsByClassName("hp-menu__item");
                if (a_menu_item.length > 0)
                    a_menu_item[a_menu_item.length - 1].remove();
            } 
        } else
            btn.onclick = function () {
                if (param.id_current_user === 0) { //Registrar usuario
                    adq_page = param.home_url + '/account/login/';
                    window.location.replace(adq_page);
                    return;
                }
                if(param.pay_free == 1){ 
                    //Pagar tarifa de adquisición PROXIMO CAMBIO                    
                    adq_page = param.home_url + '/pago/?id_padq=' + param.post_id;
                    window.location.replace(adq_page);
                    return;
                }
                if (param._use_acq_reserved == 1 && param.allow_adq === 0 && param.total_acq_reserved < 2) {
                    var msg = "Debes comprar un comodín para adquirir productos. ¿Desea comprar un comodín?"
                    if (window.confirm(msg)) { //Pagar comodin
                        adq_page = param.home_url + '/comprar-comodin/';
                        window.location.replace(adq_page);
                        return;
                    }
                }
                if (param._use_acq_reserved == 1 && param.count_acq_reserved > 0) {//Descontar comodin
                    var msg_bono = "Se aplicará el descuento de un comodín por la adquisición seleccionada. ¿Desea realizar el descuento?";
                    if (window.confirm(msg_bono)) {
                        adq_page = param.home_url + '/descuento-por-comodin-de-reserva/?id_padq=' + param.post_id + '&orgn=1';
                        window.location.replace(adq_page);
                        return;
                    }
                }
                if (param._use_credits === 1 && param.acq_credit_pending - 1 >= 0) {//Descontar creditos
                    var msg_credit = "Se aplicará el descuento de una adquisición reservada. ¿Desea realizar el descuento?";
                    if (window.confirm(msg_credit)) {
                        adq_page = param.home_url + '/descuento-de-credito/?id_padq=' + param.post_id + '&orgn=2';
                        window.location.replace(adq_page);
                        return;
                    }
                }
                //Pagar tarifa de adquisición'                    
                adq_page = param.home_url + '/pago/?id_padq=' + param.post_id;
                window.location.replace(adq_page);



          
            };
    }
}
//Oculta el area de texto 
function hide_field_textarea() {
    var div_description = document.getElementsByClassName("hp-form__field hp-form__field--textarea"),
        field_description = document.getElementsByClassName("hp-field hp-field--textarea");
    if (div_description.length > 0) {
        var text_desc = field_description[0].textContent;
        div_description[0].hidden = true;
        field_description[0].hidden = true;
        field_description[0].value = (text_desc == '') ? '-' : text_desc;
        field_description[0].setAttribute("required", "");
    }
}
//Oculta primer elemento html encontrado en el filtro del tema hivepress por su clase css
function hide_element_hivepress_by_class(_class) {
    if (_class !== undefined) {
        var element = document.getElementsByClassName(_class);
        if (element.length > 0) {
            element[0].hidden = true;
        }
    }
}
function show_firts_character_name_vendor() {
    var element = document.getElementsByClassName("hp-vendor__name");
    if (element.length > 0) {
        element[0].children[0].outerHTML = '<h2> Anunciante: ' + element[0].children[0].innerHTML.charAt(0).toUpperCase() + '</h2>';
    }
}
//Muestra datos de la adquisicion pagada por el usuario 
function show_data_vender(data_acq) {
    var element = document.getElementsByClassName("hp-vendor__attributes hp-vendor__attributes--primary");
    if (element.length > 0) {
        element[0].outerHTML = data_acq;
    }
}
//Oculta elementos secundarios de las páginas de anuncios
function hide_secundaries_element_page_listing() {
    hide_element_hivepress_by_class("hp-listing__description");    
    hide_field_textarea();
}

function form_field_update() {
    hide_field_textarea();   
    var form_user = document.getElementsByClassName("hp-form hp-form--user-update");
    if (form_user.length > 0) {
        //div Direccion
        var div_dir = document.createElement("div"),
            label_dir = document.createElement("label"),
            input_dir = document.createElement("input");
        div_dir.setAttribute("class", "hp-form__field hp-form__field--text");
        label_dir.setAttribute("class", "hp-field__label hp-form__label");
        label_dir.innerHTML = "<span>Direcci&oacute;n</span>";
        input_dir.setAttribute("class", "hp-field hp-field--text");
        input_dir.setAttribute("type", "text");
        input_dir.setAttribute("name", "direccion");
        input_dir.setAttribute("maxlength", 80);
        input_dir.setAttribute("placeholder", 'Solo letras, espacio, _- (80 caracteres)');
        input_dir.setAttribute("required", "required");
        input_dir.setAttribute("pattern", "[A-Za-zÀ-ÿ _-]*([0-9]{3})?[A-Za-zÀ-ÿ _-]*");
        div_dir.appendChild(label_dir);
        div_dir.appendChild(input_dir);

        //div Telefono1
        var div_telef1 = document.createElement("div"),
            label_telef1 = document.createElement("label"),
            input_telef1 = document.createElement("input");
        div_telef1.setAttribute("class", "hp-form__field hp-form__field--text");
        label_telef1.setAttribute("class", "hp-field__label hp-form__label");
        label_telef1.innerHTML = "<span>T&eacute;lefono principal</span>";
        input_telef1.setAttribute("class", "hp-field hp-field--text");
        input_telef1.setAttribute("type", "text");
        input_telef1.setAttribute("name", "telef1");
        input_telef1.setAttribute("maxlength", 9);
        input_telef1.setAttribute("placeholder", 'Solo números (9 dígitos)');
        input_telef1.setAttribute("required", "required");
        input_telef1.setAttribute("pattern", "[0-9]{9}");
        div_telef1.appendChild(label_telef1);
        div_telef1.appendChild(input_telef1);

        //div telefono2
        var div_telef2 = document.createElement("div"),
            label_telef2 = document.createElement("label"),
            input_telef2 = document.createElement("input");
        div_telef2.setAttribute("class", "hp-form__field hp-form__field--text");
        label_telef2.setAttribute("class", "hp-field__label hp-form__label");
        label_telef2.innerHTML = "<span>T&eacute;lefono secundario (opcional)</span>";
        input_telef2.setAttribute("class", "hp-field hp-field--text");
        input_telef2.setAttribute("type", "text");
        input_telef2.setAttribute("name", "telef2");
        input_telef2.setAttribute("maxlength", 9);
        input_telef2.setAttribute("placeholder", 'Solo números (9 dígitos)');
        input_telef2.setAttribute("pattern", "[0-9]{9}");
        div_telef2.appendChild(label_telef2);
        div_telef2.appendChild(input_telef2);

        //div codigo postal			
        var div_cod_postal = document.createElement("div"),
            label_cod_postal = document.createElement("label"),
            input_cod_postal = document.createElement("input");
        div_cod_postal.setAttribute("class", "hp-form__field hp-form__field--text");
        label_cod_postal.setAttribute("class", "hp-field__label hp-form__label");
        label_cod_postal.innerHTML = "<span>C&oacute;digo postal</span>";
        input_cod_postal.setAttribute("class", "hp-field hp-field--text");
        input_cod_postal.setAttribute("type", "text");
        input_cod_postal.setAttribute("name", "cod_postal");
        input_cod_postal.setAttribute("maxlength", 5);
        input_cod_postal.setAttribute("placeholder", '00000');
        input_cod_postal.setAttribute("required", "required");
        input_cod_postal.setAttribute("pattern", "[0-9]{5}");
        div_cod_postal.appendChild(label_cod_postal);
        div_cod_postal.appendChild(input_cod_postal);

        // div hora de contacto
        var div_hr1 = document.createElement("div"),
            label_hr1 = document.createElement("label"),
            input_hr1 = document.createElement("input");
        div_hr1.setAttribute("class", "hp-form__field hp-form__field--text");
        label_hr1.setAttribute("class", "hp-field__label hp-form__label");
        label_hr1.innerHTML = "<span>Hora preferible de contacto del teléfono principal (Formato 24 horas)</span>";
        input_hr1.setAttribute("class", "hp-field hp-field--time");
        input_hr1.setAttribute("type", "text");
        input_hr1.setAttribute("name", "hr1_contact");
        input_hr1.setAttribute("maxlength", 8);
        input_hr1.setAttribute("placeholder", '00:00');
        input_hr1.setAttribute("required", "required");
        input_hr1.setAttribute("pattern", "((2[0-4]|1[0-9]|0?[1-9]):([0-5][0-9]))"); //"((1[0-2]|0?[1-9]):([0-5][0-9]) ?([AaPp][Mm]))"
        div_hr1.appendChild(label_hr1);
        div_hr1.appendChild(input_hr1);

        // div hora2 de contacto
        var div_hr2 = document.createElement("div"),
            label_hr2 = document.createElement("label"),
            input_hr2 = document.createElement("input");
        div_hr2.setAttribute("class", "hp-form__field hp-form__field--text");
        label_hr2.setAttribute("class", "hp-field__label hp-form__label");
        label_hr2.innerHTML = "<span>Hora preferible de contacto del teléfono secundario (Formato 24 horas)</span>";
        input_hr2.setAttribute("class", "hp-field hp-field--time");
        input_hr2.setAttribute("type", "text");
        input_hr2.setAttribute("name", "hr1_contact");
        input_hr2.setAttribute("maxlength", 8);
        input_hr2.setAttribute("placeholder", '00:00');
        input_hr2.setAttribute("pattern", "((2[0-4]|1[0-9]|0?[1-9]):([0-5][0-9]))");
        div_hr2.appendChild(label_hr2);
        div_hr2.appendChild(input_hr2);

        //Agregando input al form
        var div_form = document.getElementsByClassName("hp-form__fields"),
            input_email = document.getElementsByClassName("hp-form__field hp-form__field--email");
        div_form[1].appendChild(div_dir);
        div_form[1].appendChild(div_cod_postal);
        div_form[1].appendChild(div_telef1);
        div_form[1].appendChild(div_hr1);
        div_form[1].appendChild(div_telef2);
        div_form[1].appendChild(div_hr2);

        //en caso de que exista una descripcion desagregarla en los campos direccion, codigo postal, telefono1 y telefono2
        var input_desc = document.getElementsByName("description");
        if (input_desc[0].value != '') {
            console.log(input_desc);
            var values = input_desc[0].value.split('|');
            input_dir.value = (values[0] == undefined) ? '' : values[0];
            input_cod_postal.value = (values[1] == undefined) ? '' : values[1];
            input_telef1.value = (values[2] == undefined) ? '' : values[2];
            input_telef2.value = (values[3] == undefined) ? '' : values[3];
            input_hr1.value = (values[4] == undefined) ? '' : values[4];
            input_hr2.value = (values[5] == undefined) ? '' : values[5];
        }
        //Ajustando valor del input field_description antes del submit
        var btn_submit = document.getElementsByClassName("hp-form__button button alt button hp-field hp-field--submit");
        btn_submit[1].onclick = function () {
            input_desc[0].value = input_dir.value + '|' + input_cod_postal.value + '|' + input_telef1.value + '|' + input_telef2.value;
            input_desc[0].value += '|' + input_hr1.value + '|' + input_hr2.value;
            console.log(input_desc[0].value);
        }

    }
}

function hide_options_post_listing(reserved) {
    if (reserved > 0) {
        var div_action_listing = document.getElementsByClassName("hp-listing__actions hp-listing__actions--secondary");
        if (div_action_listing.length > 0)
            div_action_listing[0].remove();
    } else {
        var div_action_hide = document.getElementsByClassName("hp-listing__action hp-listing__action--hide hp-link");
        if (div_action_hide.length > 0)
            div_action_hide[0].remove();
    }
}

//Mostrando mensaje de error si existen campos vacios del usuario.
function show_msg_error_data_contact_null(data_contact_is_null){
     var div_msg = document.getElementsByClassName("hp-form__messages hp-form__messages--error");

     if (div_msg.length > 0) {
         div_msg[0].setAttribute("class", "hp-form__messages hp-form__messages--error");
 
         if (data_contact_is_null == 1) {
             div_msg[0].innerHTML = '<p>Debe completar los datos de contactos antes de publicar un anuncio.</p>';
             div_msg[0].style = "display: block;";
         } else {
            div_msg[0].innerHTML = '';
            div_msg[0].style = "";
         }
     }
}

function show_data_admin(page_url){
    let element = document.getElementsByClassName('hp-widget widget widget_nav_menu hp-menu hp-menu--user-account');
    let elem_li = document.createElement("li");
    elem_li.setAttribute("class", "hp-menu__item hp-menu__item--user-edit-settings");   
    
    let elem_a = document.createElement("a"); 
    elem_a.innerHTML = '<span>Ventas</span>'; 
    elem_a.setAttribute("href", page_url + '/listado-ventas/');
    
    elem_li.appendChild(elem_a);
    
    if (element.length > 0) {
        element[0].children[0].append(elem_li);
    }
    
}

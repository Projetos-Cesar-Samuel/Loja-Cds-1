/**
 * Copyright [2014] [Dexxtz]
 *
 * @package   Dexxtz_Customaddress
 * @author    Dexxtz
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

function maskDexxtz(o, f) {
    v_obj = o
    v_fun = f
    setTimeout('mask()', 1)
}

function mask() {
    v_obj.value = v_fun(v_obj.value)
}

function maskTelephone(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
    v = v.replace(/(\d)(\d{4})$/, "$1 - $2");
    return v;
}

function maskZipBrazil(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/^(\d{5})(\d)/g, "$1-$2");
    return v;
}

function getEndereco() { 
	if(jQuery.trim(jQuery('#billing-new-address-form .zip_autocomplete').val()) != ''){ 
		jQuery.getScript('https://webservice.kinghost.net/web_cep.php?formato=javascript&auth=b14a7b8059d9c055954c92674ce60032&cep=' +jQuery('.zip_autocomplete').val(), function(){ 
			if (resultadoCEP['resultado'] > 0){ 
				var tip = unescape(resultadoCEP.tipo_logradouro);
				var end = unescape(resultadoCEP.logradouro);
				var bai = unescape(resultadoCEP.bairro);
				var cid = unescape(resultadoCEP.cidade); 
				var est = unescape(resultadoCEP.uf);
				
				// jQuery('#billing-new-address-form .state_autocomplete option:selected').removeAttr('selected');
				
				var info = getStates();
				for(var i in info){					
					if(info[i].code == est){
						jQuery('#billing-new-address-form .state_autocomplete').val(info[i].id);					
					}
				}
				
				jQuery('#billing-new-address-form .street1_autocomplete').val(end ? tip+ ' ' + end : tip);
				jQuery('#billing-new-address-form .street3_autocomplete').val(bai); 
				jQuery('#billing-new-address-form .city_autocomplete').val(cid); 
				jQuery('#billing-new-address-form .street1_autocomplete').focus(); 
			} else { 
				jQuery('#billing-new-address-form .street1_autocomplete').val(''); 
				jQuery('#billing-new-address-form .street3_autocomplete').val(''); 
				jQuery('#billing-new-address-form .city_autocomplete').val(''); 
				jQuery('#billing-new-address-form .street1_autocomplete').val(''); 
			} 
		}); 
	}
}

function getEnderecoShipping() { 
	if(jQuery.trim(jQuery('#shipping-new-address-form .zip_autocomplete').val()) != ''){ 
		jQuery.getScript('https://webservice.kinghost.net/web_cep.php?formato=javascript&auth=b14a7b8059d9c055954c92674ce60032&cep=' +jQuery('#shipping-new-address-form .zip_autocomplete').val(), function(){ 
			if (resultadoCEP['resultado'] > 0){ 
				var tip = unescape(resultadoCEP.tipo_logradouro);
				var end = unescape(resultadoCEP.logradouro);
				var bai = unescape(resultadoCEP.bairro);
				var cid = unescape(resultadoCEP.cidade); 
				var est = unescape(resultadoCEP.uf);
				
				// jQuery('#shipping-new-address-form .state_autocomplete option:selected').removeAttr('selected');
					
				var info = getStates();
				
				for(var i in info){

					if(info[i].code == est){
						jQuery('#shipping-new-address-form .state_autocomplete').val(info[i].id);
						// jQuery('#shipping-new-address-form .state_autocomplete').attr('value',info[i].name);
						// jQuery('#shipping-new-address-form .state_autocomplete option').each(function(){
						// 	if(jQuery(this).val() == info[i].id){
						// 		jQuery(this).attr('selected','selected');	
						// 	}
						// });
					}
				}
				
				jQuery('#shipping-new-address-form .street1_autocomplete').val(end ? tip+ ' ' + end : tip);
				jQuery('#shipping-new-address-form .street3_autocomplete').val(bai); 
				jQuery('#shipping-new-address-form .city_autocomplete').val(cid); 
				jQuery('#shipping-new-address-form .street1_autocomplete').focus(); 
			} else { 
				jQuery('#shipping-new-address-form .street1_autocomplete').val(''); 
				jQuery('#shipping-new-address-form .street3_autocomplete').val(''); 
				jQuery('#shipping-new-address-form .city_autocomplete').val(''); 
				jQuery('#shipping-new-address-form .street1_autocomplete').val(''); 
			} 
		}); 
	}
}
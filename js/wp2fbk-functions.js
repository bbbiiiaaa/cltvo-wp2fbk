var cltvo_wp2fbk;
(function($) {
	$(document).ready(function(){

		function Cltvo_Wp2Fbk (){
			var cltvo_FB = false;
			var user_data = false;
			var nuevo_comment = false;

			//console.log(cltvo_FB);

			inicia = function(extCallback){

				if(cltvo_FB){
					extCallback();
				}else{
					$.getScript('//connect.facebook.net/es_LA/all.js', function(){
						FB.init({
							appId: wp2fbk_vars.appid,
							status: true,
							xfbml: false
						});
						cltvo_FB = FB;

						autentica(function(){
							extCallback();
						});
					});
				}
			}

			autentica = function(autCallback){
				cltvo_FB.getLoginStatus(function(response) {

					if (response.status === 'connected') {
						get_user_data(function(){ autCallback(); });

					} else if (response.status === 'not_authorized') {

						cltvo_FB.login(function(response) {
							if (response.authResponse) {
								get_user_data(function(){ autCallback(); });
							}
						}, {scope: 'publish_stream'});

					} else {
						cltvo_FB.login(function(response) {
							if (response.authResponse) {
								get_user_data(function(){ autCallback(); });
							}
						}, {scope: 'publish_stream'});
						
					}
				});
			}

			get_user_data = function(get_user_data_cback){
				if(!user_data){
					cltvo_FB.api('/me', function(response) {
						user_data = response;
						get_user_data_cback();
					});
				}
			}

			this.cltvo_fbk_like = function(jqObj){
				inicia(function(){
					var fid = jqObj.attr('fbk-post-id');
					var a_cambiar = $('.cltvo-wp2fbk-like-cambiar-JS[fbk-post-id="'+fid+'"]');

					cltvo_FB.api('/'+fid+'/likes', 'post',function(response) {
						if(response === true) {
							a_cambiar.text(parseInt(a_cambiar.text())+1);
						}
					});
				});
			}

			this.cltvo_fbk_borra = function(fid){
				cltvo_FB.api('/'+fid, 'delete', function(response) {
					//console.log(response);
					if(response === true){
						nuevo_comment.remove();
					}
				});
			}

			this.cltvo_fbk_comenta = function(args){
				//console.log(args);

				inicia(function(){
					//console.log(user_data);
					var fid = args.fid;
					var mensj = args.mensj;

					cltvo_FB.api('/'+fid+'/comments', 'post', { message: mensj }, function(response) {
						//console.log(response);
						if(!response.error){
							var newfid = response.id;
							var html = '';
							var a_cambiar = $('.todos-comentarios[fbk-post-id="'+fid+'"]');

							html = '<div class="comentarios" fbk-post-id="'+newfid+'">';
							html += '<img src="http://graph.facebook.com/'+user_data.id+'/picture" />';
							html += '<p class="nombre-face">'+user_data.name+'</p>';
							html += '<p class="comentario">'+mensj+'</p>';
							html += '<a href="#" class="cltvo-wp2fbk-borrar-JS pd-JS" fbk-post-id="'+newfid+'">borrar</a>';
							html += '</div>';

							a_cambiar.prepend(html);

							nuevo_comment = $('.comentarios[fbk-post-id="'+newfid+'"]');
						}

					});
				});				
			}
		}

		cltvo_wp2fbk = new Cltvo_Wp2Fbk(); 
		
	});
})(jQuery);
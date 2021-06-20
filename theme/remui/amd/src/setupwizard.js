define([
	'jquery', 
	'core/modal_factory', 
	'core/modal_events', 
	'core/config',
	'core/ajax',
	'core/notification',
	'core/str'
	], function($, ModalFactory, ModalEvents, Config, Ajax, notification, str) {

	// component selectors
	var swContainer = ".edw.setupwizard-container"

	var swFormContainer = swContainer + " #adminsettings";

	var swStepContainer =  swContainer + " .steps-index";
	var swStep = swStepContainer + " .step";
	var swCurrentStep = swStepContainer + " .step.current";
	var swActiveStep = swStepContainer + " .step.active";

	var swStepDataContainer = swContainer+ " .step-data";
	var swFieldSet = swStepDataContainer + " .adminsettings";
	var swFieldSetcurrent = swFieldSet + ".current";
	var swFieldSetActive = swFieldSet + ".active";
	

	var swNextButton = swContainer + " .next-step";
	var swPrevButton = swContainer + " .prev-step";
	var swContinueButton = swContainer + " .continue";

	// Licensing Elements
	var swlicenseContainer = swContainer + " .license-box";
	var swLicenseButton = swlicenseContainer + " .license-btn";

	var swActivateButton = swLicenseButton + '[name="edd_remui_license_activate"]';
	var swDeactivateButton = swLicenseButton + '[name="edd_remui_license_deactivate"]';

	var swlSessKey = swlicenseContainer + ' input[name="sesskey"]';
	var swlLicensePage = swlicenseContainer + ' input[name="onLicensePage"]';
	var swlLicenseKey = swlicenseContainer + ' input[name="edd_remui_license_key"]';

	var swlicenseloader = swlicenseContainer + ' .licensing-loader';

	let sw;

	var langstrings;

	var SetupWizard = function(stepCount, stepData) {
	    this.totalSteps = stepCount;
		this.stepStatus = stepData;

		for (var i = 0; i < stepData.length; i++) {
		  if (stepData[i].isactive) {
		  	this.activeStep = i + 1;
		  }
		  if (stepData[i].status == "current") { 
		  	this.currentStep = i + 1;	
		  }
		}
	}
	$.extend(SetupWizard.prototype, {
		registerEvents: function() {
			var _this = this;

			$(document).on('click', swNextButton,function(){
				if (_this.activeStep < _this.totalSteps) {
					if (_this.activeStep == _this.currentStep) {
						// Current step is active step - then update the step 
						_this.saveCurrentStep(_this.activeStep);
					} else {
						// If the previous button is clicked.
						_this.saveCurrentStep(-1);
					}
					_this.activeStep = _this.activeStep + 1; 
					_this.alterStep(_this.activeStep);
					_this.updateView(_this);
				}
			});

			$(document).on('click', swPrevButton,function(){
				if (_this.activeStep > 1) {
					_this.activeStep = _this.activeStep - 1; 
					_this.alterStep(_this.activeStep);
					_this.updateView(_this);
				}
			});

			$(document).on('click', swActivateButton,function(e){
				e.preventDefault();
				_this.serveLicenseData('activate');
			});

			$(document).on('click', swDeactivateButton,function(e){
				e.preventDefault();
				_this.serveLicenseData('deactivate');
			});
		},
		saveCurrentStep: function(step){
			var formData = jQuery(swFormContainer).serializeArray();
			var licenseajax = Ajax.call([{
			    methodname: "theme_remui_save_current_step",
			    args: {
			    	"step": step,
			        "settings": JSON.stringify(formData),
			    }
			}]);
			licenseajax[0].done(function(response) {
								
			}).fail(function(ex) {
				// _this.showResponse(ex);
				var exception =  ex.message.substring(12, ex.message.length);
			    // do something with the exception
			});
		},
		serveLicenseData: function(action){
			var _this = this;
			// Activate loader
			$(swlicenseloader).css('display', 'inline-block'); // show method adds display block as css property, we want inline-block.
			var onLicensePage = $(swlLicensePage).val();
			var sessKey = $(swlSessKey).val();
			var licenseKey = $(swlLicenseKey).val();

			var $regexname=/^([a-z0-9]{32})$/;
			if (licenseKey.match($regexname)) {
				
				var licenseajax = Ajax.call([{
				    methodname: "theme_remui_serve_license_data",
				    args: {
				        "licensekey": licenseKey, 
				        "sesskey": sessKey, 
				        "action": action, 
				        "onlicensepage": onLicensePage
				    }
				}]);
				licenseajax[0].done(function(response) {
					response = JSON.parse(response);
					$(swlicenseloader).css('display', 'none'); // hide the loader
					_this.showResponse(response);
				}).fail(function(ex) {
					var exception =  ex.message.substring(12, ex.message.length);
					exception = JSON.parse(exception);
					
					_this.showResponse({
						"license": 'failed',
						"msg": exception.msg,
						"error":true
					});

					$(swlicenseloader).css('display', 'none');
				});
			}
			else{
				_this.showResponse({
					"license": 'failed',
					"msg": langstrings[21],
					"error":true
				});
				$(swlicenseloader).css('display', 'none');
			}
		},
		showResponse: function(response){
			var _this = this;

			// This object will define the License Status On click Event.
			var responseObj = { 
				"alert": {
					"classes": "alert alert-danger form-group license-alert",
					"icon": "icon fa fa-ban",
					"heading": langstrings[24], // Alert 
					"text": langstrings[14] // License key is deactivated.
				},
				"licensekey": {
					"classes": "form-control is-invalid"
				}, 
				"status": {
					"color": "red",
					"text": langstrings[5]
				}, 
				"buttons": {
					"0": {
						"type": "submit",
						"class": "btn btn-success text-white license-btn",
						"name": "edd_remui_license_activate",
						"value": langstrings[2],
					}
				}
			};

			if (response.success == false && typeof response.error !== "undefined" && response.error === "expired") {
				responseObj.alert.text = langstrings[19]; // Your license key has Expired. Please, Renew it.
				responseObj.status.text = langstrings[8]; // Expired
				
				responseObj.buttons = {
					"0": {
						"type": "submit",
						"class": "btn btn-success text-white license-btn mr-1",
						"name": "edd_remui_license_deactivate",
						"value": langstrings[3],
					},
					"1": {
						"type": "submit",
						"class": "btn btn-info text-white license-btn",
						"name": "edd_remui_license_renew",
						"value": langstrings[4],
						'onclick': "window.open('https://edwiser.org')"
					}
				};
				// enable the renew license button.
			} else if (response.success == false 
				&& response.license == "invalid" 
				&& typeof response.error !== "undefined" 
				&& response.error === "disabled") {

				responseObj.alert.text = langstrings[18]; // Your license key is disabled.
				responseObj.status.text = langstrings[23]; // Disabled
				responseObj.buttons = {
					"0": {
						"type": "submit",
						"class": "btn btn-success text-white license-btn",
						"name": "edd_remui_license_activate",
						"value": langstrings[2],
					}
				};

			} else if (response.success == false 
				&& response.license == 'invalid' 
				&& typeof response.error !== "undefined" 
				&&  response.error == "no_activations_left") {

			    responseObj.alert.text = langstrings[17]; // nolicenselimitleft
			    responseObj.status.text = langstrings[11]; // Limit Exceed
			    responseObj.buttons = {
			    	"0": {
			    		"type": "submit",
			    		"class": "btn btn-success text-white license-btn",
			    		"name": "edd_remui_license_activate",
			    		"value": langstrings[2],
			    	}
			    };
			} else if (response.success == false 
				&& response.license == 'invalid' 
				&& typeof response.error !== "undefined" 
				&&  response.error == "item_name_mismatch") {

				responseObj.alert.text = langstrings[27]; // Invalid key.
				responseObj.status.text = langstrings[26]; // Limit Exceed
				responseObj.buttons = {
					"0": {
						"type": "submit",
						"class": "btn btn-success text-white license-btn",
						"name": "edd_remui_license_activate",
						"value": langstrings[2],
					}
				};
			} else if (response.license == 'failed' || response.license == "deactivated") {
			    responseObj = { 
    				"alert": {
    					"classes": "alert alert-danger form-group license-alert",
    					"icon": "icon fa fa-ban",
    					"heading": langstrings[24],
    					"text": (typeof response.msg !== "undefined")? response.msg: langstrings[14],
    				},
    				"licensekey": {
    					"classes": "form-control is-invalid"
    				},
    				"status": {
    					"color": "red",
    					"text": langstrings[5]
    				}, 
    				"buttons": {
    					"0": {
    						"type": "submit",
    						"class": "btn btn-success text-white license-btn",
    						"name": "edd_remui_license_activate",
    						"value": langstrings[2],
    					}
    				}
    			};
			} else if (response.success == true && response.license == "valid" && response.activations_left > 0) {
			    responseObj = { 
    				"alert": {
    					"classes": "alert alert-success form-group license-alert",
    					"icon": "icon fa fa-check",
    					"heading": langstrings[25],
    					"text": langstrings[20]
    				},
    				"licensekey": {
    					"classes": "form-control is-valid"
    				},
    				"status": {
    					"color": "green",
    					"text": langstrings[6]
    				}, 
    				"buttons": {
    					"0": {
    						"type": "submit",
    						"class": "btn btn-danger text-white license-btn",
    						"name": "edd_remui_license_deactivate",
    						"value": langstrings[3],
    					}
    				}
    			};
			}
			_this.updateLicensingView(responseObj);

		},
		updateLicensingView: function(obj){
			// Alert Code
			var alertText = '<div class="'+ obj.alert.classes +'"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h4><i class="'+ obj.alert.icon +'"></i> <span class="subtext">'+ obj.alert.heading +'</span></h4> <span class="subtext">'+ obj.alert.text +'</span></div>'; 

			if(jQuery(swlicenseContainer + " .box-container .license-alert").length) {
				// replace the alert if already present.
				jQuery(swlicenseContainer + " .box-container .license-alert").replaceWith(alertText);
			} else {
				// Add new alert if already not present.
				jQuery(swlicenseContainer + " .box-container").prepend(alertText);
			}
			// Alert Code

			// License Key Code
			jQuery(swlicenseContainer + " .box-container .licensekey #edd_remui_license_key").removeClass().addClass(obj.licensekey.classes);
			// License Key Code

			// Status Code
			var statusText = '<div class="col-12 text"><p style="color:'+ obj.status.color +'">'+ obj.status.text +'</p></div>'; 
			jQuery(swlicenseContainer + " .box-container .licensestatus .text").replaceWith(statusText);
			// Status Code

			// Buttons Code
			var buttons = "";

			jQuery.each(obj.buttons, function( index, value ) {
				var html = "<input ";
			    jQuery.each(value, function(attr, attrVal){
			        html += attr + '="' + attrVal + '" ';
			    });

			    html += ">";
			    buttons += html;
			});
			// Adding loader html, as we are going to replace it.
			buttons += '<div class="licensing-loader spinner-border text-muted hidden"></div>';

			jQuery(swlicenseContainer + " .box-container .licensebuttons .license-form-btn").empty().append(buttons);
			// Buttons Code

		},
		updateView: function(){
			$(swNextButton).prop('disabled', false);
			$(swPrevButton).prop('disabled', false);
			$(swNextButton).show();
			$(swContinueButton).hide();
			if (this.activeStep == this.totalSteps) {
				// $(swNextButton).prop('hidden', true);
				$(swNextButton).hide();
				$(swContinueButton).show();
			}

			if (this.activeStep == 1) {
				$(swPrevButton).prop('disabled', true);
				$(swNextButton).show();
				$(swContinueButton).hide();
			}
		},
		alterStep: function(stepid){
			// Check if current step is Active one, and need to be updated as done
			// and check if previous button is not clicked by comparison with currentstep
			if ($(swActiveStep).hasClass('current') && stepid > this.currentStep) {
				$(swActiveStep).removeClass('current').addClass("done");
				$(swStep + '[data-stepid="' + stepid + '"]').addClass('current');
				$(swFieldSet + '[data-stepid="' + stepid + '"]').addClass('current');
				this.currentStep = this.currentStep + 1; // Update the current id
			}
			$(swActiveStep).removeClass('active');
			$(swFieldSetActive).removeClass('active');
			$(swStep + '[data-stepid="' + stepid + '"]').addClass('active');
			$(swFieldSet + '[data-stepid="' + stepid + '"]').addClass('active');

			gotopFunction();
		},
		requireLangStrings: function() {
			// We will get array of string without key -- instead we get it as an index.
			// So do not interchange the places of strings in following strings array.
			// Addition of Extra strings can be done at end of array.
			var strings = [
		        { key: 'licensenotactive', component: 'theme_remui'}, // 0 
		        { key: 'licensenotactiveadmin', component: 'theme_remui'}, // 1
		        { key: 'activatelicense', component: 'theme_remui'}, // 2
		        { key: 'deactivatelicense', component: 'theme_remui'}, // 3
		        { key: 'renewlicense', component: 'theme_remui'}, // 4
		        { key: 'deactivated', component: 'theme_remui'}, // 5
		        { key: 'active', component: 'theme_remui'}, // 6
		        { key: 'notactive', component: 'theme_remui'}, // 7
		        { key: 'expired', component: 'theme_remui'}, // 8
		        { key: 'licensekey', component: 'theme_remui'}, // 9
		        { key: 'licensestatus', component: 'theme_remui'}, // 10
		        { key: 'no_activations_left', component: 'theme_remui'}, // 11
		        { key: 'activationfailed', component: 'theme_remui'}, // 12
		        { key: 'noresponsereceived', component: 'theme_remui'}, // 13
		        { key: 'licensekeydeactivated', component: 'theme_remui'}, // 14
		        { key: 'siteinactive', component: 'theme_remui'}, // 15
		        { key: 'entervalidlicensekey', component: 'theme_remui'}, // 16
		        { key: 'nolicenselimitleft', component: 'theme_remui'}, // 17
		        { key: 'licensekeyisdisabled', component: 'theme_remui'}, // 18
		        { key: 'licensekeyhasexpired', component: 'theme_remui'},  // 19
		        { key: 'licensekeyactivated', component: 'theme_remui'}, // 20
		        { key: 'entervalidlicensekey', component: 'theme_remui'}, // 21
		        { key: 'edwiserremuilicenseactivation', component: 'theme_remui'}, // 22
		        { key: 'disabled', component: 'theme_remui'}, // 23
		        { key: 'alert', component: 'theme_remui'}, // 24
		        { key: 'success', component: 'theme_remui'}, // 25
		        { key: 'invalid', component: 'theme_remui'}, // 26
		        { key: 'purchasecodeinvalid', component: 'theme_remui'}, // 27
		    ];
		    str.get_strings(strings).then(function (results) {
		        langstrings = results;
		    });
		}
	});
	var skipUpgrade = function() {
		// Add the button to redirect to setup wizard page from upgradation page.
		// Consider few things before doing it...
		// 1. What if other plugins are also being updated.
		//    It should not skip other plugins config setup.
		// 2. Should not stop the upgrade.php file execution.
		// 3. Do not hide other plugin's settings.
		var remuisettings = '#page-admin-upgradesettings #admin-enableannouncement';
		jQuery(remuisettings).parent().prev().hide();
		jQuery(remuisettings).parent().hide();
	};

	// When the user clicks Next button, scroll to the top of the document
	function gotopFunction() {
		$('html, body').animate({scrollTop: 0}, $(window).scrollTop() / 6);
	}

	var promptsetupwizard = function(){
		var strings = [
	        { key: 'setupwizard', component: 'theme_remui'}, // 0 
	        { key: 'setupwizardmodalmsg', component: 'theme_remui'}, // 1
	    ];
	    str.get_strings(strings).then(function (results) {
    		ModalFactory.create({
    			type: ModalFactory.types.SAVE_CANCEL,
                title: results[0],
                body: results[1]
            })
            .then(function(modal) {
    	        modal.setSaveButtonText(results[0]);
    	        var root = modal.getRoot();
    	        root.on(ModalEvents.save, function() {
    	            // Do something on Setup Wizard button click
    	            Ajax.call([{
                        methodname: 'theme_remui_set_setting',
                        args: {configname: "flagsetupwizard", configvalue: true},
                        done: function(){
                        	$(location).attr('href', Config.wwwroot + '/theme/remui/setupwizard.php');
                        },
                        fail: notification.exception
                    }]);
    	            
    	        });
    	        root.on(ModalEvents.cancel, function() {
    	            // Do something on Setup Wizard button click
    	            Ajax.call([{
                        methodname: 'theme_remui_set_setting',
                        args: {configname: "flagsetupwizard", configvalue: true},
                        done: function(){},
                        fail: notification.exception
                    }]);
    	        });
            	modal.show();
    	    });
	    });

		
	};

	return {
	    init: function(stepData){

	    	// This is a hack, as quotes were being converted to &quot --
	    	stepData = JSON.parse(stepData.replace(/&quot;/g, '"'));
	    	 	
	    	// Initialize the object
	    	sw = new SetupWizard($('.stepscount').val(), stepData);
	    	// Register all the click and document load events required on SetupWizard
	    	sw.registerEvents();
	    	
	    	sw.updateView();
	    	sw.requireLangStrings();
	    },
	    skipUpgrade: skipUpgrade,
	    prompt: promptsetupwizard
	};
});

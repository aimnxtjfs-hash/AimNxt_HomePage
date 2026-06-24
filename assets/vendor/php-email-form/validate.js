/**
 * PHP Email Form Validation - v3.6
 * URL: https://bootstrapmade.com/php-email-form/
 * Author: BootstrapMade.com
 */
(function () {
  "use strict";
  let forms = document.querySelectorAll('.php-email-form');
 forms.forEach(function (e) {
    e.addEventListener('submit', function (event) {
      
      event.preventDefault();
      

      let thisForm = this;

      let action = thisForm.getAttribute('action');

      let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');

      if (!action) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }

      
  let form_data = new FormData(thisForm);
   let submit_type=event.submitter.name;
      
  if(submit_type=="send_otp_type"){
    thisForm.querySelector('.invalid').classList.add('d-none');
    thisForm.querySelector('.send_otp_button').classList.remove('d-block');
    var button = document.getElementById('send_otp');
button.disabled = true;
    setTimeout(() => {
      thisForm.querySelector('.send_otp_button').classList.add('d-none');
      thisForm.querySelector('.resend_otp_button').classList.remove('d-none');
      thisForm.querySelector('.resend_otp_button').classList.add('d-block');
    }, 10000);
    thisForm.querySelector('.loading').classList.remove('d-none');
    thisForm.querySelector('.loading').classList.add('d-block');
    form_data.set('sendotp', 'yes');
    send_otp(thisForm, action, form_data);
    return ;
  }else if(submit_type=="verify_otp_type"){
    thisForm.querySelector('.loading').classList.remove('d-none');
    thisForm.querySelector('.loading').classList.add('d-block');
    thisForm.querySelector('.invalid').classList.add('d-none');

    form_data.set('verify_otp_type', 'yes');
    verify_otp(thisForm, action, form_data);
    
    return ;

  }
      else if (thisForm.id == "course-registration-form" ||
         thisForm.id == "registration-form" || thisForm.id == "enrollModal") {

      //  alert('here...........')
        // add enrollment form too.

        thisForm.querySelector('.loading').classList.remove('d-block');
          thisForm.querySelector('.loading').classList.add('d-none');
        thisForm.querySelector('.error-message').classList.remove('d-none');
        thisForm.querySelector('.sent-message').classList.remove('d-block');
      }
      if (thisForm.id == "career-guidance-modal-form" || 
        thisForm.id == "curriculam-modal-form") {

        thisForm.querySelector('.loading').classList.remove('d-none');
        thisForm.querySelector('.loading').classList.add('d-block');
      }
      if (thisForm.id == "networking-assessment-form") {

        thisForm.querySelector('.loading').classList.remove('d-none');
        thisForm.querySelector('.loading').classList.add('d-block');
      }

      let formData = new FormData(thisForm);

      if (recaptcha) {
        if (typeof grecaptcha !== "undefined") {
          grecaptcha.ready(function () {
            try {
              grecaptcha.execute(recaptcha, {
                action: 'php_email_form_submit'
              })
                .then(token => {
                  formData.set('recaptcha-response', token);
                  php_email_form_submit(thisForm, action, formData);
                })
            } catch (error) {
              displayError(thisForm, error);
            }
          });
        } else {
          displayError(thisForm, 'The reCaptcha javascript API url is not loaded!')
        }
      } else {
        php_email_form_submit(thisForm, action, formData);
      }
    });
  });

  function php_email_form_submit(thisForm, action, formData) {

    //alert(action)

    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => {
      //console.log(response)
        if (response.ok) {
      
          return response.text();
        } else {
          throw new Error(`${response.status} ${response.statusText} ${response.url}`);
        }
      })

      .then(data => {
        //alert(data)
        // if (typeof curriculamModal != 'undefined') curriculamModal.hide();
        if (typeof careerGuidanceModal != 'undefined') careerGuidanceModal.hide();
        if (typeof courseContentModal != 'undefined') courseContentModal.hide();

        console.log(thisForm.id);

        // check if its a download form.
        if (thisForm.id == "curriculam-modal-form" ||
           thisForm.id == "course-content-modal-form" ||
            thisForm.id == "career-guidance-modal-form") {

          thisForm.querySelector('.loading').classList.remove('d-block');
          thisForm.querySelector('.loading').classList.add('d-none');
          thisForm.querySelector('.sent-message').classList.remove('d-none');
          thisForm.querySelector('.sent-message').classList.add('d-block');
          if (data.trim() == 'OK') {
            thisForm.reset();
          }

          if (fileName != null) {
            const tag = document.createElement("a");
            const path = './course-content/' + fileName + "-content.pdf";
            console.log(path);
            tag.href = path;
            tag.download = fileName + ".pdf";
            tag.target = "_blank";
            tag.click();
          }
          setTimeout(function () {
            thisForm.querySelector('.sent-message').classList.add('d-none');    
          }, 2500);
          curriculamModal.hide();
          thisForm.reset();
        }
        
        else if (thisForm.id == "networking-assessment-form") {
  
          thisForm.querySelector('.assessment-score').classList.remove('d-none');
          thisForm.querySelector('.networkAssesment').classList.add('d-none');
          thisForm.querySelector('.score').textContent = data.match(/\d+/) + "/15";
          thisForm.querySelector('.assessment-score').style.display = 'block';

          thisForm.querySelector('.loading').classList.remove('d-block');
          thisForm.querySelector('.loading').classList.add('d-none');
          // setTimeout(() => { 
          //   thisForm.querySelector('.assessment-score').classList.add('d-none');
          //   thisForm.reset();
          // }, 7000);;

        }
        else if (thisForm.id === "enrollModal" ) {
            
          thisForm.querySelector('.loading').classList.remove('d-block');
          thisForm.querySelector('.loading').classList.add('d-none');
          thisForm.querySelector('.sent-message').classList.remove('d-none');
          thisForm.querySelector('.sent-message').classList.add('d-block'); //remove


         setTimeout(function () {

            thisForm.querySelector('.sent-message').classList.add('d-none');

            //toastBootstrap.hide();

          }, 2500);
          thisForm.reset();
          //toastBootstrap.hide();
        } else {

       //   alert('finally')
          thisForm.querySelector('.loading').classList.remove('d-block');
          if (data.trim() == 'OK' || data.trim() == 'Thanks') {
            thisForm.querySelector('.sent-message').classList.add('d-block');
          
            thisForm.reset();
          } else {
            throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action);
          }
        }
      })
      .catch((error) => {
        console.log(error);
        displayError(thisForm, error);
      });
  }

  function verify_otp(thisForm, action, formData) {


    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          throw new Error(`${response.status} ${response.statusText} ${response.url}`);
        }
      })

      .then(data => {

        
      
        // check if its a download form.
          if (data.trim() == 'OK') {
            thisForm.querySelector('.loading').classList.remove('d-block');

          if(fileName != null){
            const tag = document.createElement("a");
            const path = './course-content/'+fileName+"-content.pdf";
            console.log(path);
            tag.href = path;
            tag.download = fileName+".pdf";
            tag.target = "_blank";
            tag.click();
            thisForm.querySelector('.sent-message').classList.remove('d-none');
            thisForm.querySelector('.sent-message').classList.add('d-block'); 
            setTimeout(() => { 
              curriculamModal.hide();
              thisForm.reset();
              thisForm.querySelector('.sent-message').classList.remove('d-block');
          thisForm.querySelector('.sent-message').classList.add('d-none');
            }, 1500);
            thisForm.querySelector('.resend_otp_button').classList.add('d-none');
            thisForm.querySelector('.send_otp_button').classList.remove('d-none');
            thisForm.querySelector('.send_otp_button').classList.add('d-block');
            var button = document.getElementById('send_otp');
            button.disabled = false;
          
      
          }
         
        } else if(data.trim() == "failure"){
          thisForm.querySelector('.loading').classList.remove('d-block');
          thisForm.querySelector('.invalid').classList.remove('d-none');
          thisForm.querySelector('.invalid').classList.add('d-block');
        }
        
      
      })
      .catch((error) => {
        console.log(error);
        displayError(thisForm, error);
      });
  }

  function displayError(thisForm, error) {
    console.log(error);
    if (thisForm.id === "careerModal") {

      careerModal.hide();

    } else if (thisForm.id === "enrollModal") {
      toastBootstrap.hide();

    } else {
      thisForm.querySelector('.loading').classList.remove('d-block');
      thisForm.querySelector('.error-message').innerHTML = error;
      thisForm.querySelector('.error-message').classList.add('d-block');
    }
  }



  function send_otp(thisForm, action, formData) {


    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          throw new Error(`${response.status} ${response.statusText} ${response.url}`);
        }
      })

      .then(data => {
        thisForm.querySelector('.loading').classList.remove('d-block');
        thisForm.querySelector('.verify_button').classList.remove('d-none');
        thisForm.querySelector('.verify_button').classList.add('d-block');
       
      })
      .catch((error) => {
        console.log(error);
        displayError(thisForm, error);
      });
     
  }

})();
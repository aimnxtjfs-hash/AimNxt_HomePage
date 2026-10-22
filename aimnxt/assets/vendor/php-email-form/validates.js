
(function () {
  "use strict";
  let forms = document.querySelectorAll('.php-email-form');
 forms.forEach(function (e) {
    e.addEventListener('submit', function (event) {
      
      event.preventDefault();
      

      let thisForm = this;

      let action = thisForm.getAttribute('action');


      if (!action) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }

      
  let form_data = new FormData(thisForm);
   
    if (thisForm.id == "course-registration-form" ||
          thisForm.id =='nx-uniform-form' || thisForm.id =='demoForm' ||
           thisForm.id == "registration-form" || thisForm.id == "enrollModal") {

      //  alert('here...........')
        // add enrollment form too.

        thisForm.querySelector('.loading').style.display="block";
         thisForm.querySelector('.error-message').style.display="none";
        thisForm.querySelector('.sent-message').style.display="none";
      }
   
      let formData = new FormData(thisForm);

      php_email_form_submit(thisForm, action, formData);
     
    });
  });

  function php_email_form_submit(thisForm, action, formData) {

    console.log(action)
console.log(formData)
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
      
        console.log(thisForm.id);
     console.log(data);
        if (data.trim() == 'OK' || data.trim()=='Thanks') {
           thisForm.querySelector('.loading').style.display="none";
         thisForm.querySelector('.error-message').style.display="none";
        thisForm.querySelector('.sent-message').style.display="block";
      
            thisForm.reset();
          } else {
            throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action);
          }
        }
      )
      .catch((error) => {
        console.log(error);
        displayError(thisForm, error);
      });
  }

 
  function displayError(thisForm, error) {
    thisForm.querySelector('.loading').style.display="none";
         thisForm.querySelector('.error-message').style.display="block";
        thisForm.querySelector('.sent-message').style.display="none";
      
    thisForm.querySelector('.error-message').innerHTML = error;
    
  }



 
})();
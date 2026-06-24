if(document.getElementById("career-guidance-modal")){
   var careerGuidanceModal  = new bootstrap.Modal('#career-guidance-modal', {});
}

if(document.getElementById("curriculam-modal")){
  var  curriculamModal  = new bootstrap.Modal('#curriculam-modal', {});
}

if(document.getElementById("course-content-modal")){
   var courseContentModal  = new bootstrap.Modal('#course-content-modal', {});
}

const myTimeout = setTimeout(modalPop, 2000);

function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function modalPop() {

  cName = getCookie(cName);  
    if (cName != 1) {
      if(careerGuidanceModal) careerGuidanceModal.show()
    }
  
}


// 공통 JavaScript 함수들 - 노토 폰트 환경에 최적화

// MM (Macromedia) 함수들 - 레거시 지원
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function WEBSILDESIGNWINDOW(theURL, width, height, scrollbars) {
  var features = 'width=' + width + ',height=' + height + ',scrollbars=' + scrollbars + ',resizable=yes,toolbar=no,menubar=no,location=no,status=no';
  window.open(theURL, 'WebsilWindow', features);
}

// 페이지 로드 시 초기화
MM_reloadPage(true);

// 공통 유틸리티 함수들
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function validateForm(formName) {
  var form = document.forms[formName];
  if (!form) {
    console.error('폼을 찾을 수 없습니다: ' + formName);
    return false;
  }
  
  // 기본 검증 로직
  var inputs = form.querySelectorAll('input[required], select[required]');
  for (var i = 0; i < inputs.length; i++) {
    if (!inputs[i].value.trim()) {
      alert('필수 항목을 입력해주세요: ' + (inputs[i].title || inputs[i].name));
      inputs[i].focus();
      return false;
    }
  }
  
  return true;
}

// 로딩 표시 함수
function showLoading() {
  var loading = document.getElementById('loading');
  if (!loading) {
    loading = document.createElement('div');
    loading.id = 'loading';
    loading.innerHTML = '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.8);color:white;padding:20px;border-radius:5px;z-index:9999;font-family:\'Noto Sans KR\',sans-serif;">처리 중...</div>';
    document.body.appendChild(loading);
  }
  loading.style.display = 'block';
}

function hideLoading() {
  var loading = document.getElementById('loading');
  if (loading) {
    loading.style.display = 'none';
  }
}

// AJAX 헬퍼 함수
function ajaxRequest(url, method, data, callback) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          var response = JSON.parse(xhr.responseText);
          callback(null, response);
        } catch (e) {
          callback(e, xhr.responseText);
        }
      } else {
        callback(new Error('HTTP ' + xhr.status + ': ' + xhr.statusText), null);
      }
    }
  };
  
  xhr.open(method.toUpperCase(), url, true);
  
  if (method.toUpperCase() === 'POST') {
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(data);
  } else {
    xhr.send();
  }
}

// 쿠키 관리 함수
function setCookie(name, value, days) {
  var expires = "";
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function eraseCookie(name) {
  document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}
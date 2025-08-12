<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width"/>
<title>PlusFriend Add Friend Button Demo - Kakao JavaScript SDK</title>
<script src="//developers.kakao.com/sdk/js/kakao.min.js"></script>

</head>
<body>
<div id="plusfriend-addfriend-button"></div>
http://pf.kakao.com/_pEGhj
<script type='text/javascript'>
  //<![CDATA[
    // 사용할 앱의 JavaScript 키를 설정해 주세요.
    Kakao.init('a145422e4ae4163d4efcb54c22bb1af2');
    // 플러스친구 친구추가 버튼을 생성합니다.
    Kakao.PlusFriend.createAddFriendButton({
      container: '#plusfriend-addfriend-button',
      plusFriendId: '_pEGhj' // 플러스친구 홈 URL에 명시된 id로 설정합니다.
    });
  //]]>
</script>

</body>
</html>
document.addEventListener('DOMContentLoaded', function() {
    const menu = [
        {
            title: "HELP",
            items: [
                { label: "업데이트사항", url: "/HELP/SoftUpgrade.php" },
                { label: "WEBSIL바로가기", url: "http://www.websil.net", target: "_blank" }
            ]
        },
        {
            title: "관리자환경",
            items: [
                { label: "비밀번호 변경", url: "/admin/AdminConfig.php?mode=modify" }
            ]
        },
        {
            title: "게시판관리",
            items: [
                { label: "생성/관리/삭제", url: "/admin/bbs_admin.php?mode=list" },
                { label: "자료신고함", url: "/admin/BBSSinGo/index.php" },
                { label: "실적물 관리", url: "/admin/results/admin.php?mode=list" }
            ]
        },
        {
            title: "회원관리",
            items: [
                { label: "LIST/검색/관리", url: "/admin/member/index.php" },
                {
                    label: "메일관리",
                    items: [
                        { label: "가입완료메일", url: "/admin/member/JoinAdmin.php" },
                        { label: "전체메일 관리", url: "/admin/member/MaillingJoinAdmin.php" },
                        {
                            label: "전체 메일발송",
                            items: [
                                { label: "YES만 발송", url: "/admin/mailing/form.php?FFF=ok" },
                                { label: "전체다 발송", url: "/admin/mailing/form.php" }
                            ]
                        }
                    ]
                }
            ]
        },
        {
            title: "인쇄업무프로그램",
            items: [
                {
                    label: "견적안내프로그램",
                    items: [
                        { label: "전단지 관리", url: "/admin/MlangPrintAuto/inserted_List.php" },
                        { label: "스티카 관리", url: "/admin/MlangPrintAuto/sticker_List.php" },
                        { label: "명함 관리", url: "/admin/MlangPrintAuto/NameCard_List.php" },
                        { label: "상품권 관리", url: "/admin/MlangPrintAuto/MerchandiseBond_List.php" },
                        { label: "봉투 관리", url: "/admin/MlangPrintAuto/envelope_List.php" },
                        { label: "양식지 관리", url: "/admin/MlangPrintAuto/NcrFlambeau_List.php" },
                        { label: "카다로그 관리", url: "/admin/MlangPrintAuto/cadarok_List.php" },
                        // { label: "카다로그 관리", url: "/admin/MlangPrintAuto/cadarokTwo_List.php" }, // 필요시 주석 해제
                        { label: "소량인쇄 관리", url: "/admin/MlangPrintAuto/LittlePrint_List.php" },
                        { label: "견적안내 주문", url: "/admin/MlangPrintAuto/OrderList.php" },
                        { label: "시안직접올리기", url: "/admin/MlangPrintAuto/admin.php?mode=AdminMlangOrdert", target: "Mlang" },
                        { label: "견적안내 통합관리", url: "/admin/MlangPrintAuto/admin.php?mode=BankForm&code=Text", target: "Mlang" }
                    ]
                },
                {
                    label: "수동견적프로그램",
                    items: [
                        { label: "수동견적 주문", url: "/admin/MlangPrintAuto/OfferOrder.php" }
                    ]
                },
                {
                    label: "인쇄관련 업무",
                    items: [
                        { label: "주문자 접수일보", url: "/admin/MlangPrintAuto/MemberOrderOfficeList.php" }
                    ]
                }
            ]
        }
    ];

    const nav = document.getElementById('mainMenu');
    const ul = document.createElement('ul');
    ul.className = 'main-menu';

    menu.forEach(section => {
        appendMenuItem(ul, section, true);
    });

    nav.appendChild(ul);

    function appendMenuItem(parent, item, isTopLevel = false) {
        const li = document.createElement('li');
        li.className = isTopLevel ? 'menu-group top-level' : 'menu-group';

        const a = document.createElement('a');
        a.className = 'menu-item';
        a.textContent = item.title || item.label;
        a.href = item.url || "#";
        if (item.target) a.target = item.target;
        li.appendChild(a);

        // 하위 메뉴(items/children) 재귀 처리
        let subItems = [];
        if (item.items && item.items.length > 0) subItems = subItems.concat(item.items);
        if (item.children && item.children.length > 0) subItems = subItems.concat(item.children);

        if (subItems.length > 0) {
            const ul = document.createElement('ul');
            ul.className = "submenu-group";
            subItems.forEach(child => appendMenuItem(ul, child, false));
            li.appendChild(ul);

            // 드롭다운: 마우스 오버시 open 클래스 토글
            li.addEventListener('mouseenter', () => li.classList.add('open'));
            li.addEventListener('mouseleave', () => li.classList.remove('open'));
        }

        parent.appendChild(li);
    }
});
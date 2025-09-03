document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const choiceForm = document.forms.choiceForm;
    if (!choiceForm) {
        console.error("주문 폼(choiceForm)을 찾을 수 없습니다.");
        return;
    }

    const elements = {
        myType: choiceForm.MY_type,
        myFsd: choiceForm.MY_Fsd,
        pnType: choiceForm.PN_type,
        myAmount: choiceForm.MY_amount,
        orderType: choiceForm.ordertype,
        parentList: choiceForm.parentList,
        printPrice: document.getElementById('print_price'),
        designPrice: document.getElementById('design_price'),
        totalPrice: document.getElementById('total_price'),
        orderButton: document.querySelector('input[value="주문하기"]'),
        fileUploadButton: document.querySelector('input[value="파일올리기"]'),
        deleteFileButton: document.querySelector('input[value="삭제"]'),
        loginButton: document.querySelector('.login-btn'),
        loginModal: document.getElementById('loginModal'),
        closeModalButton: document.querySelector('.close-modal'),
        loginTabButton: document.querySelector('.login-tab[onclick*="showLoginTab"]'),
        registerTabButton: document.querySelector('.login-tab[onclick*="showRegisterTab"]'),
        loginForm: document.getElementById('loginForm'),
        registerForm: document.getElementById('registerForm'),
    };

    // --- API Abstraction ---
    const api = {
        async get(action, params) {
            const url = new URL('api.php', window.location.href);
            url.searchParams.append('action', action);
            for (const key in params) {
                url.searchParams.append(key, params[key]);
            }
            try {
                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`API GET Error (${action}):`, error);
                throw error;
            }
        },
        async post(action, data) {
            const url = new URL('api.php', window.location.href);
            url.searchParams.append('action', action);
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`API POST Error (${action}):`, error);
                throw error;
            }
        }
    };

    // --- Functions ---
    const updateSelectWithOptions = (selectElement, options, defaultOptionText = "선택하세요") => {
        selectElement.innerHTML = ''; // Clear existing options
        if (!options || options.length === 0) {
            selectElement.add(new Option(defaultOptionText, ""));
            return;
        }
        options.forEach(opt => {
            selectElement.add(new Option(opt.title, opt.no));
        });
    };

    const calculatePrice = async () => {
        const { myType, pnType, myFsd, myAmount, orderType } = elements;
        if (!myType.value || !pnType.value || !myFsd.value || !myAmount.value) {
            console.log("가격 계산에 필요한 값들이 비어있음");
            return;
        }

        const params = {
            MY_type: myType.value,
            PN_type: pnType.value,
            MY_Fsd: myFsd.value,
            MY_amount: myAmount.value,
            ordertype: orderType.value,
        };

        try {
            const response = await api.post('calculatePrice', params);
            if (response.success && response.data) {
                elements.printPrice.textContent = response.data.Price + "원";
                elements.designPrice.textContent = response.data.DS_Price + "원";
                elements.totalPrice.textContent = response.data.Order_Price + "원";

                // Update hidden fields
                choiceForm.PriceForm.value = response.data.PriceForm;
                choiceForm.DS_PriceForm.value = response.data.DS_PriceForm;
                choiceForm.Order_PriceForm.value = response.data.Order_PriceForm;
                choiceForm.VAT_PriceForm.value = response.data.VAT_PriceForm;
                choiceForm.Total_PriceForm.value = response.data.Total_PriceForm;
                choiceForm.StyleForm.value = response.data.StyleForm;
                choiceForm.SectionForm.value = response.data.SectionForm;
                choiceForm.QuantityForm.value = response.data.QuantityForm;
                choiceForm.DesignForm.value = response.data.DesignForm;
            } else {
                alert("가격 계산 실패: " + (response.message || "알 수 없는 오류"));
            }
        } catch (error) {
            alert("가격 계산 중 오류가 발생했습니다.");
        }
    };

    const updateDependentFields = async (categoryType) => {
        console.log(`updateDependentFields called with categoryType: ${categoryType}`); // Added log
        elements.myFsd.innerHTML = '<option value="">로딩중...</option>';
        elements.pnType.innerHTML = '<option value="">로딩중...</option>';

        try {
            const [sizes, papers] = await Promise.all([
                api.get('getSizes', { category_type: categoryType }),
                api.get('getPapers', { category_type: categoryType })
            ]);

            updateSelectWithOptions(elements.myFsd, sizes, "규격을 선택하세요");
            updateSelectWithOptions(elements.pnType, papers, "종이종류를 선택하세요");

            await calculatePrice();
        } catch (error) {
            updateSelectWithOptions(elements.myFsd, [], "규격 로드 실패");
            updateSelectWithOptions(elements.pnType, [], "종이 로드 실패");
            alert("옵션을 불러오는 중 오류가 발생했습니다.");
        }
    };

    const submitOrder = (mode) => {
        if (!elements.myType.value || !elements.pnType.value || !elements.myFsd.value || !elements.myAmount.value) {
            alert("모든 옵션을 선택해주세요.");
            return false;
        }
        if (!choiceForm.Order_PriceForm.value || choiceForm.Order_PriceForm.value === "0") {
            alert("가격 정보가 없습니다. 옵션을 다시 선택하여 가격을 계산해주세요.");
            return false;
        }
        choiceForm.action = `/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=${mode}`;
        choiceForm.submit();
    };

    // --- File Upload Functions ---
    const openFileUploadWindow = () => {
        // These phpVars should be available globally or passed differently.
        // For now, assuming they are on the window object from a script tag in the HTML.
        const { log_url, log_y, log_md, log_ip, log_time } = window.phpVars || {};
        if (!log_url) {
            alert("파일 업로드에 필요한 정보가 없습니다.");
            return;
        }
        const url = `../../PHPClass/MultyUpload/FileUp.php?Turi=${log_url}&Ty=${log_y}&Tmd=${log_md}&Tip=${log_ip}&Ttime=${log_time}&Mode=tt`;
        window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
    };

    const deleteSelectedFiles = () => {
        for (let i = elements.parentList.options.length - 1; i >= 0; i--) {
            if (elements.parentList.options[i].selected) {
                elements.parentList.remove(i);
            }
        }
    };
    
    // This function needs to be called from the popup window.
    // It should be exposed globally.
    window.addToParentList = (srcList) => {
      for (let i = 0; i < srcList.options.length; i++) {
        if (srcList.options[i] != null) {
          elements.parentList.add(new Option(srcList.options[i].text, srcList.options[i].value));
        }
      }
    };

    // --- Modal Functions ---
    const showLoginModal = () => elements.loginModal.style.display = 'block';
    const hideLoginModal = () => elements.loginModal.style.display = 'none';
    const switchTab = (isLogin) => {
        elements.loginForm.style.display = isLogin ? 'block' : 'none';
        elements.registerForm.style.display = isLogin ? 'none' : 'block';
        elements.loginTabButton.classList.toggle('active', isLogin);
        elements.registerTabButton.classList.toggle('active', !isLogin);
    };

    // --- Event Listeners ---
    elements.myType.addEventListener('change', () => updateDependentFields(elements.myType.value));
    [elements.myFsd, elements.pnType, elements.myAmount, elements.orderType].forEach(el => {
        el.addEventListener('change', calculatePrice);
    });

    elements.orderButton.addEventListener('click', () => submitOrder('OrderOne'));
    elements.fileUploadButton.addEventListener('click', openFileUploadWindow);
    elements.deleteFileButton.addEventListener('click', deleteSelectedFiles);

    if (elements.loginButton) {
        elements.loginButton.addEventListener('click', showLoginModal);
    }
    if (elements.closeModalButton) {
        elements.closeModalButton.addEventListener('click', hideLoginModal);
    }
    if (elements.loginTabButton) {
        elements.loginTabButton.addEventListener('click', () => switchTab(true));
    }
    if (elements.registerTabButton) {
        elements.registerTabButton.addEventListener('click', () => switchTab(false));
    }
    window.addEventListener('click', (event) => {
        if (event.target === elements.loginModal) {
            hideLoginModal();
        }
    });

    // --- Initial Load ---
    if (elements.myType.value) {
        updateDependentFields(elements.myType.value);
    }
});
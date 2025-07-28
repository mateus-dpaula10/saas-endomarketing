document.addEventListener('DOMContentLoaded', function() {
    const formsLogin = document.getElementById('form-login')
    const btnEsqueceuLogin = document.getElementById('esqueceu-senha-login')
    const formsEsqueceuLogin = document.getElementById('form-esqueceu-senha')
    const btnLogin = document.getElementById('voltar-login')
    let textInitalLogin = 1
    const titulo = document.querySelector('#bloco-login h2')
    const inputPassword = document.getElementById('password')
    const btnShowPassword = document.getElementById('icon_fa_eye')

    verificaTexto()

    if (btnEsqueceuLogin) {
        btnEsqueceuLogin.addEventListener('click', (e) => {
            e.preventDefault();
            formsLogin.style.display = 'none';
            formsEsqueceuLogin.style.display = 'flex';
            textInitalLogin = 0;
            verificaTexto()
        })
    }

    if (btnLogin) {
        btnLogin.addEventListener('click', (e) => {
            e.preventDefault();
            formsEsqueceuLogin.style.display = 'none';
            formsLogin.style.display = 'flex';
            textInitalLogin = 1;
            verificaTexto()
        })
    }

    function verificaTexto() {
        titulo.innerText = textInitalLogin === 1 ? 'Acesse sua conta' : 'Recupere sua conta'
    }

    if (btnShowPassword) {
        btnShowPassword.addEventListener('click', function() {
            if (inputPassword.type === 'password') {
                inputPassword.type = 'text';
                btnShowPassword.classList.remove('bi-eye-fill');
                btnShowPassword.classList.add('bi-eye-slash-fill');
            } else {
                inputPassword.type = 'password';
                btnShowPassword.classList.remove('bi-eye-slash-fill');
                btnShowPassword.classList.add('bi-eye-fill');
            }
        })
    }
})
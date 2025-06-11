document.addEventListener('DOMContentLoaded', () => {
    const formsLogin = document.getElementById('form-login')
    const btnEsqueceuLogin = document.getElementById('esqueceu-senha-login')
    const formsEsqueceuLogin = document.getElementById('form-esqueceu-senha')
    const btnLogin = document.getElementById('voltar-login')
    let textInitalLogin
    const titulo = document.querySelector('#bloco-login h2')

    btnEsqueceuLogin.addEventListener('click', (e) => {
        e.preventDefault()
        formsLogin.style.display = 'none'
        formsEsqueceuLogin.style.display = 'flex'
        textInitalLogin = 0
        verificaTexto()
    })

    btnLogin.addEventListener('click', (e) => {
        e.preventDefault()
        formsEsqueceuLogin.style.display = 'none'
        formsLogin.style.display = 'flex'
        textInitalLogin = 1
        verificaTexto()
    })

    function verificaTexto() {
        titulo.innerText = textInitalLogin === 1 ? 'Acesse sua conta' : 'Recupere sua conta'
    }
})
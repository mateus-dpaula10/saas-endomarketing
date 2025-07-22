document.addEventListener('DOMContentLoaded', () => {
    const aside = document.querySelector('aside')
    const toggleAside = document.querySelector('#close-menu')

    toggleAside.addEventListener('click', () => {
        const marginLeft = window.getComputedStyle(aside).marginLeft

        if (marginLeft === '0px') {
            aside.style.marginLeft = '-240px'    
        } else {
            aside.style.marginLeft = '0px'    
        }
    })

    // const alerts = document.querySelectorAll('.alert')
    // alerts.forEach(alert => {
    //     if (alert) {
    //         setTimeout(() => {
    //             alert.style.display = 'none'
    //         }, 5000)
    //     }
    // })
})
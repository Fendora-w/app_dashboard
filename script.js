$(document).ready(() => {
    
    
    $('#documentacao').on('click', () => {
        // $('#pagina').load('documentacao.html')

        /*$.get('documentacao.html', data => {
            $('#pagina').html(data)
        })*/

        $.post('documentacao.html', data => {
            $('#pagina').html(data)
        })

    })

    $('#suporte').on('click', () => {
        // $('#pagina').load('suporte.html')

        /*$.get('suporte.html', data => {
            $('#pagina').html(data)
        })*/

        $.post('suporte.html', data => {
            $('#pagina').html(data)
            
        })

    })

    
    //ajax
    $('#competencia').on('change', e => {

        let competencia = $(e.target).val()
        console.log(competencia)

        
        $.ajax({
           type: 'GET',
           url: 'app.php',
           data: `competencia=${competencia}`, //x-www-from-urlencode
           dataType: 'json',
           success: dados => { //acessando os dados
            $('#numeroVendas').html(dados.numeroVendas),
            $('#totalVendas').html(dados.totalVendas),
            $('#clientesAtivos').html(dados.clientesAtivos),
            $('#clientesInativos').html(dados.clientesInativos),
            $('#totalCriticas').html(dados.totalCriticas),
            $('#totalElogios').html(dados.totalElogios),
            $('#totalSugestoes').html(dados.totalSugestoes),
            $('#totalDespesas').html(dados.totalDespesas)
            
           },
           error: erro => {/*console.log(erro)*/}
        })

        //método, url, dados, sucesso, error
    })


})
function showContent(id) {
    if(document.getElementById(id + '-content').style.display == 'none') {
        document.getElementById(id + '-content-short').style.display = 'none';
        document.getElementById(id + '-content').style.display = 'block';
        document.getElementById(id + '-more').innerHTML = '...감추기';
    } else {
        document.getElementById(id + '-content-short').style.display = 'block';
        document.getElementById(id + '-content').style.display = 'none';
        document.getElementById(id + '-more').innerHTML = '...더보기';
    }
}

/*Заполняем файлменеджер*/
bin2hex = function(bin){           // UTF-8 string -> ASCII hex
     var hex = '';
     for(var i = 0; i<bin.length; i++){
       var c = bin.charCodeAt(i);
       if (c>0xFF) c -= 0x350;              // UTF-8 -> ASCII
       hex += c.toString(16);
    }
     return hex;
}
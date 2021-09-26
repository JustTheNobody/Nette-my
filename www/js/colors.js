
        var x = document.querySelectorAll ('.radio');
        var colors = ['#0066ff', '#ff3300', '#33cc33', '#ffff00'];

        for(y=0; y<x.length; y++){
                z = x[y].parentNode;
                z.style.backgroundColor = colors[y];                
                z.style.borderRadius = '5px';
        }

        td = x[0].parentNode.parentNode;           
        console.log(td);
        td.classList.add("flexTest");

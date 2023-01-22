/**
 * Translates strings...
* @todo: Replace all strings to translate with the trans function
 */
const { __, _x, _n, _nx } = wp.i18n;

let plek_lang = {

    de_DE : {
        'Photo rights - What are you allowed to do' : 'Fotorechte - Was du machen darfst',
        'Share Page' : 'Seite teilen',
        'Share Photo' : 'Foto teilen',
        'Save Photo' : 'Speichere Foto',
        'Copy link to Photo' : 'Kopiere link zum Foto',
        'Close' : 'Schliessen',
    },

    construct(){
        
    },

    /**
     * Translates a String
     * @param {string} string 
     * @returns string The translated string
     */
    trans(string){

        if(typeof this.de_DE[string] !== 'undefined'){
            return this.de_DE[string];
        }
        return string;
    }
   

    
    
}

plek_lang.construct();

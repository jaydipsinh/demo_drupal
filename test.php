<script>

var arr = [10,20,30,100,20,10,1000,20,50];
var abc = find_max(arr);
alert(abc);
function find_max(nums) {
 let max_num = Number.NEGATIVE_INFINITY; // smaller than all other numbers
 for (let num of nums) {
 if (num > max_num) {
 // (Fill in the missing line here)
	max_num = num;
 }
 }
 return max_num;
 }
 
 </script>
<?php 
    /*Name of the document file*/
    $document = 'test.docx';
	$filename= 'test.docx';

    /**Function to extract text*/
    function extracttext($filename) {
        //Check for extension

        $extOrg = explode('.', $filename);
		$ext = $extOrg[1];
    //if its docx file
    if($ext == 'docx')
    $dataFile = "word/document.xml";
    //else it must be odt file
    else
    $dataFile = "content.xml";     

    //Create a new ZIP archive object
    $zip = new ZipArchive;

    // Open the archive file
    if (true === $zip->open($filename)) {
        // If successful, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // Index found! Now read it to a string
            $text = $zip->getFromIndex($index);
            // Load XML from a string
            // Ignore errors and warnings
// create a DOMDocument instance
$doc = new DOMDocument();

// call the method on the instance
$doc->loadXML($text, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

// then print the XML 
echo $doc->saveXML();exit;

            $xml = DOMDocument::loadXML($text, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Remove XML formatting tags and return the text
            return strip_tags($xml->saveXML());
        }
        //Close the archive file
        $zip->close();
    }

    // In case of failure return a message
    return "File not found";
}

echo extracttext($document);
exit;

//echo phpinfo();
?>
# phpmwbutil

A library that reads MySQLWorkbench files into structured classes.

## Example

```
$document = new PHPMwbUtil\MwbDocument();
$document->loadFile('example01.mwb');
var_dump($document->physicalModels);
var_dump($document->data);
```

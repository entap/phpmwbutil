# phpmwbutil

A library that reads MySQLWorkbench files into structured classes.

## Example

```
$loader = new \PHPMwbUtil\MwbLoader();
$loader->load('example01.mwb');
var_dump($loader->document->physicalModels);
var_dump($loader->data);
```

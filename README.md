# Buto-Plugin-WfYml

YML file reader.

## Usage

```
$form = new PluginWfYml(__DIR__.'/form/customer.yml');
```


## Method

### setByTag

Yml data

```
-
  type: span
  innerHTML: rs:minutes
-
  type: span
  innerHTML: rs:calc_date_to/minutes
```

```
$element->setByTag(array('minutes' => 33, 'calc_date_to' => array('minutes' => 44)));
```

```
-
  type: span
  innerHTML: 33
-
  type: span
  innerHTML: 44
```

### sort
Sort data.

Params.
- key (optional, default null), if sort by a key.
- desc (optional, default false) if sort descending.
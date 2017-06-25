acf.add_filter('select2_args', function(select2_args, $select, args, $field){
  if (jQuery($field).hasClass('js-unforgettable-select')) {
    select2_args.tags = true;
    select2_args.selectOnBlur = true;
  }

  return  select2_args;
});

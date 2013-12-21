# GetSimple-ContentFields

Manage Form fields for the GetSimple Cms

## Requirements

- jquery-ui and jquery-ui-sortable

## Todo

- Read the "Dropdown Box" options as CSV
- Add options for the checkbox field
  - If no options, value is 1
  - othwerise it's a key => value
- Add a (simple) way to make a field mandatory (or eventually add other filters)
- Add a hint on the possible values for the options, different for each type.
- Is there a way to make the labels translatable?
- Implement radiobuttons (very similar to dropdown lists)
- Is there a way to store some fields in an encrypted way?

# Features

## Text field
## Long text field
## Textarea

- The options define in an URL format which buttons will be available in the edit toolbar, the height of the textarea, and wether the "no-paragraph" mode is enabled
      toolbar=Bold,Italic,Link,Unlink&height=300px
  - The default toolbar can be defined with the constants:
    - `CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_ADVANCED`, the buttons for the advanced toolbar,
    - `CONTENTFIELDS_FIELD_TEXTAREA_TOOLBAR_BASIC`, the buttons for the basic toolbar,
  - If toolbar is not defined you won't get any toolbar, if it's set to `basic` or `advanced` one of the two predefined ones (`toolbar=basic`).
  - The default height is defined through the constant `CONTENTFIELDS_FIELD_TEXTAREA_HEIGHT` (by default "200px")
', '200px');


## Dropdown lists

- In their simplest form, the options are a list of labels, where each line is one option
- Use a CSV list to define the value and labels

## Checkboxes

- In their simplest form, the options are a list of labels, where each line is one option.
- Use a CSV list to define the value and labels


## File upload

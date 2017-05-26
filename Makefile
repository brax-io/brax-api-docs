
.PHONY: preview

apiary.apib: blueprint/tables/.apib blueprint/.apib

blueprint/.apib:
	hercule hercule.md -o apiary.apib

preview: apiary.apib
	apiary preview

blueprint/tables/.apib:
	php data_structure_tables.php


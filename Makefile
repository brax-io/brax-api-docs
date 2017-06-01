
.PHONY: preview

apiary.apib: blueprint/tables/.apib blueprint/.apib

blueprint/.apib:
	hercule hercule.md -o apiary.apib
	dos2unix apiary.apib

preview: apiary.apib
	apiary preview

blueprint/tables/.apib:
	php data_structures.php


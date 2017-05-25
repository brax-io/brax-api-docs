
.PHONY: preview

blueprint/.apib:
	hercule hercule.md -o apiary.apib

preview:
	apiary preview


.PHONY: preview

blueprint/.apib:
	hercule hercule.md -o apiary.apib

preview: blueprint/.apib
	apiary preview

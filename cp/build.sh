# https://blog.filippo.io/shrink-your-go-binaries-with-this-one-weird-trick/
# https://github.com/pwaller/goupx
go build -ldflags="-s -w" lepus.go
./goupx lepus

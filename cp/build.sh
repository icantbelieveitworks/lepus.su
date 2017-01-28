# https://blog.filippo.io/shrink-your-go-binaries-with-this-one-weird-trick/
# for golang 1.3.3
# https://github.com/pwaller/goupx
# export GOPATH=~/lepuscp
# go build -ldflags="-s -w" lepus.go
# ./goupx lepus
#
# https://golang.org/dl/
# tar -C /usr/local -xzf go1.7.5.linux-amd64.tar.gz

/usr/local/go/bin/gofmt -w lepus.go
/usr/local/go/bin/go build -ldflags="-s -w" lepus.go
upx lepus

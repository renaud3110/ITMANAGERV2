//go:build !windows

package main

import (
	"fmt"
	"os"
)

func main() {
	cfg, err := loadConfig()
	if err != nil {
		fmt.Fprintf(os.Stderr, "Erreur: %v\n", err)
		os.Exit(1)
	}

	fmt.Println("IT Manager - Agent Moniteur")
	fmt.Printf("Intervalle: %d secondes\n", cfg.Interval)
	RunLoop(cfg)
}
